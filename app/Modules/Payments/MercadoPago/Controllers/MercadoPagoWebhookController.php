<?php

namespace App\Modules\Payments\MercadoPago\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Payment;
use App\Modules\Payments\MercadoPago\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use MercadoPago\Client\Invoice\InvoiceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Resources\PreApproval\Summarized;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Example:
        // $type = 'payment.created'
        // $method = 'handlePaymentCreated'

        PaymentService::setup();

        $type = $request->string('type')->toString();
        $method = 'handle' . Str::studly($type);

        if (method_exists($this, $method)) {
            return $this->$method($request);
        }

        return $this->notImplementedResponse($request);
    }

    protected function handlePayment(Request $request)
    {
        $orderId = $request->input('data.id');

        $client = new PaymentClient;

        $payment = $client->get($orderId);

        // Do not handle preapproval payments here
        if (isset($payment->metadata['preapproval_id'])) {
            return;
        }

        // Extract Candidate ID from external reference (PATH-1234)
        $candidateId = preg_match('/PATH-(\d+)/', $payment->external_reference, $matches)
            ? $matches[1]
            : $payment->external_reference;

        Payment::firstOrCreate([
            'payment_method' => 'mercado_pago',
            'payment_id' => $orderId,
        ], [
            'candidate_id' => $candidateId,
            'currency' => $payment->currency_id,
            'amount' => $payment->transaction_amount,
            'status' => 'approved',
            'current_period' => CarbonImmutable::parse($payment->date_approved)->day(1),
        ]);

        $candidate = Candidate::find($candidateId);
        $candidate->save();

        return response()->json(['status' => 'success']);
    }

    protected function handleSubscriptionPreapproval(Request $request)
    {
        // Do not handle new subscriptions, as they need to be approved by the user first.
        // The handleSubscriptionAuthorizedPayment method will handle the subscription after it has been approved
        // and the first payment has been made.
        return $this->successResponse($request);

        // $preapprovalId = $request->input('data.id');

        // $preapproval = (new PreApprovalClient)
        //     ->get($preapprovalId);

        // /** @var Summarized $preapprovalSummary */
        // $preapprovalSummary = $preapproval
        //     ->summarized;


        // $candidateId = preg_match('/PATH-(\d+)/', $preapproval->external_reference, $matches)
        //     ? $matches[1]
        //     : null;

        // /** @var Candidate $candidate */
        // $candidate = Candidate::withCount('payments')
        //     ->findOrFail($candidateId);

        // if ($candidate->payments_count !== 0) {
        //     report(new \Exception('preapproval already exists' . $request->json()));
        //     throw new \Exception('preapproval already exists');
        // }
        // $monthlyAmount = ($preapprovalSummary->charged_amount + $preapprovalSummary->pending_charge_amount) / ($preapprovalSummary->charged_quantity + $preapprovalSummary->pending_charge_quantity);

        // $dateCreated = CarbonImmutable::parse($preapproval->date_created);

        // $payments = collect()->range(from: 1, to: $preapprovalSummary->quotas)
        //     ->map(fn (int $installment) => [
        //         'candidate_id' => $preapproval->external_reference,
        //         'payment_method' => 'mercado_pago',
        //         'payment_id' => $preapprovalId,
        //         'currency' => 'ARS',
        //         'amount' => $monthlyAmount,
        //         'installment_number' => $candidate->installments,
        //         'current_installment' => $installment,
        //         'status' => 'pending',
        //         'current_period' => $dateCreated->addMonths($installment)->day(1),
        //     ]);

        // $candidate->payments()->createMany($payments);

        // return $this->successResponse($request);
    }

    public function handleSubscriptionAuthorizedPayment(Request $request)
    {
        $invoiceId = $request->input('data.id');

        $invoiceClient = new InvoiceClient;

        $invoice = $invoiceClient->get($invoiceId);

        $preapprovalId = $invoice->preapproval_id;

        // Get preapproval (subscription)
        $preapproval = (new PreApprovalClient)
            ->get($preapprovalId);

        if ($preapproval->status !== 'authorized') {
            report(new \Exception('Preapproval not authorized' . $request->json()));
            abort(400, 'Preapproval not authorized');
        }

        /** @var Summarized $preapprovalSummary */
        $preapprovalSummary = $preapproval
            ->summarized;

        $candidateId = preg_match('/PATH-(\d+)/', $preapproval->external_reference, $matches)
            ? $matches[1]
            : null;

        /** @var Candidate $candidate */
        $candidate = Candidate::findOrFail($candidateId);

        // Check if the candidate already has payments
        if (
            $candidate->payments()
            ->wherePaymentId($preapprovalId)
            ->count() != $preapprovalSummary->quotas
        ) {
            // Delete existing payments for this preapproval
            $candidate->payments()
                ->wherePaymentId($preapprovalId)
                ->delete();

            $monthlyAmount = ($preapprovalSummary->charged_amount + $preapprovalSummary->pending_charge_amount) / ($preapprovalSummary->charged_quantity + $preapprovalSummary->pending_charge_quantity);

            $dateCreated = CarbonImmutable::parse($preapproval->date_created);

            $payments = collect()->range(from: 1, to: $preapprovalSummary->quotas)
                ->map(fn (int $installment) => [
                    'candidate_id' => $preapproval->external_reference,
                    'payment_method' => 'mercado_pago',
                    'payment_id' => $preapprovalId,
                    'currency' => 'ARS',
                    'amount' => $monthlyAmount,
                    'installment_number' => $preapprovalSummary->quotas,
                    'current_installment' => $installment,
                    'status' => 'pending',
                    'current_period' => $dateCreated->addMonths($installment)->day(1),
                ]);

            $candidate->payments()->createMany($payments);
        }

        $candidate->payments()
            ->wherePaymentId($preapprovalId)
            ->whereNotNull('installment_number')
            ->where('current_installment', $preapprovalSummary->charged_quantity)
            ->update([
                'status' => 'approved',
                'paid_date' => CarbonImmutable::parse($preapprovalSummary->last_charged_date),
            ]);

        return $this->successResponse($request);
    }

    protected function successResponse()
    {
        return response()->json(['status' => 'success']);
    }

    protected function notImplementedResponse(Request $request)
    {
        report(new \Exception('Webhook not implemented' . $request->json()));
        return response()->json(['status' => 'not_implemented'], 501);
    }
}
