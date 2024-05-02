<?php

namespace App\Modules\Payments\MercadoPago\Jobs;

use App\Models\Candidate;
use App\Models\Payment;
use App\Modules\Payments\MercadoPago\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use MercadoPago\Client\Invoice\InvoiceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Resources\PreApproval\Summarized;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle()
    {
        // Example:
        // $type = 'payment.created'
        // $method = 'handlePaymentCreated'

        PaymentService::setup();

        $payload = $this->webhookCall->payload;
        $type = $payload['type'];
        $method = 'handle' . Str::studly($type);

        if (method_exists($this, $method)) {
            $this->$method($payload);
        }
    }

    protected function handlePayment(array $data)
    {
        $orderId = $data['id'];

        $client = new PaymentClient;

        $payment = $client->get($orderId);

        $metadata = (object) $payment->metadata;
        // Do not handle preapproval payments here
        if (isset($metadata->preapproval_id)) {
            return;
        }

        $status = match ($payment->status) {
            'authorized', 'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };

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
            'status' => $status,
            'current_period' => CarbonImmutable::parse($payment->date_approved)->day(1),
        ]);

        $candidate = Candidate::find($candidateId);
        $candidate->save();

        return response()->json(['status' => 'success']);
    }

    protected function handleSubscriptionPreapproval(array $data)
    {
        // Do not handle new subscriptions, as they need to be approved by the user first.
        // The handleSubscriptionAuthorizedPayment method will handle the subscription after it has been approved
        // and the first payment has been made.
        // return $this->successResponse($request);

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

    public function handleSubscriptionAuthorizedPayment(array $data)
    {
        $invoiceId = $data['id'];

        $invoiceClient = new InvoiceClient;

        $invoice = $invoiceClient->get($invoiceId);

        $preapprovalId = $invoice->preapproval_id;

        // Get preapproval (subscription)
        $preapproval = (new PreApprovalClient)
            ->get($preapprovalId);

        if ($preapproval->status !== 'authorized') {
            report(new \Exception('Preapproval not authorized - ' . $preapprovalId));
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
            ->where('payment_id', $preapproval->id)
            ->count() != $preapprovalSummary->quotas
        ) {
            // Delete existing payments for this preapproval
            $candidate->payments()
                ->where('payment_id', $preapproval->id)
                ->delete();

            $monthlyAmount = ($preapprovalSummary->charged_amount + $preapprovalSummary->pending_charge_amount) / ($preapprovalSummary->charged_quantity + $preapprovalSummary->pending_charge_quantity);

            $dateCreated = CarbonImmutable::parse($preapproval->date_created);

            $payments = collect()->range(from: 1, to: $preapprovalSummary->quotas)
                ->map(fn (int $installment) => [
                    'candidate_id' => $preapproval->external_reference,
                    'payment_method' => 'mercado_pago',
                    'payment_id' => $preapproval->id,
                    'currency' => 'ARS',
                    'amount' => $monthlyAmount,
                    'installment_number' => $preapprovalSummary->quotas,
                    'current_installment' => $installment,
                    'status' => 'pending',
                    'current_period' => $dateCreated->addMonths($installment)->day(1),
                ]);

            $candidate->payments()->createMany($payments);
        }

        $payments = $candidate->payments()
            ->where('payment_id', $preapproval->id)
            ->where('current_installment', $preapprovalSummary->charged_quantity)
            ->update([
                'status' => 'approved',
                'paid_date' => CarbonImmutable::parse($preapprovalSummary->last_charged_date),
            ]);
    }
}
