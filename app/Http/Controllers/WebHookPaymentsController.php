<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\StripePaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebHookPaymentsController extends Controller
{
    public function paypalWebhook(Request $request)
    {
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');
        $payment_id = $resource['id'];
        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                $payment = Payment::where('payment_id', $resource['id'])->first();
                if ($payment != null) {
                    $payment->status = 'approved';
                    $payment->save();

                    $candidate = Candidate::find($payment->candidate_id);
                    $candidate->status = 'paid';
                    $candidate->save();
                }
                break;
            case 'PAYMENT.SALE.COMPLETED':
                if (Payment::where('payment_id', $payment_id)->first() != null) break; // payment exists

                $billing_agreement_id = $request->input('resource.billing_agreement_id');;
                $currentPayment = Payment::where('suscription_code', $billing_agreement_id)
                    ->where('status', 'pending')
                    ->orderBy('current_installment', 'ASC')
                    ->first();

                if ($currentPayment != null && $currentPayment->payment_id != null) // payment was processed
                    break;

                else if ($currentPayment  != null) {
                    $currentPayment->update(['status' => 'approved', 'payment_id' => $payment_id]);
                    Candidate::find($currentPayment->candidate_id)->update(['status' => UserStatus::Paying->value]);
                    if ($currentPayment->current_installment == $currentPayment->installment_number)
                        Candidate::find($currentPayment->candidate_id)->update(['status' => UserStatus::Paid->value]);
                    break;
                } else {
                    Log::error('currentInstallment not found');
                    break;
                }

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                Log::info('suscription activated', $request->all());
                break;

            default:
                break;
        }

        return response()->json(['status' => 'success']);
    }

    public function stripeWebhook(Request $request)
    {
        $stripePaymentMethod = new StripePaymentMethod();
        $stripePaymentMethod->processWebhook($request);

        return response()->json(['status' => 'success']);
    }

    public function mercadopagoWebhook(Request $request)
    {
        $type = $request->string('type')->toString();

        return match ($type) {
            'payment' => $this->handleMercadoPagoPayment($request),
            'subscription_preapproval' => $this->handleMercadoPagoSubscription($request),
            'subscription_authorized_payment' => $this->handleMercadoPagoSubscriptionPayment($request),
            default => $this->notImplementedResponse($request),
        };
    }

    private function handleMercadoPagoPayment(Request $request)
    {
        $orderId = $request->input('data.id');
        $token = config('mercadopago.access_token');

        $url = 'https://api.mercadopago.com/v1/payments/' . $orderId;

        $response = Http::withToken($token)
            ->get($url);

        if ($response->failed()) {
            return response()->json(['status' => 'payment_not_found'], 500);
        }

        $result = $response->body();
        $data = json_decode($result, true);

        $candidateId = $data['external_reference'];

        Payment::firstOrCreate([
            'payment_method' => 'mercado_pago',
            'payment_id' => $orderId,
        ], [
            'candidate_id' => $candidateId,
            'currency' => $data['currency_id'],
            'amount' => $data['additional_info']['items'][0]['unit_price'],
            'status' => 'approved',
        ]);

        $candidate = Candidate::find($candidateId);
        $candidate->status = 'paid';
        $candidate->save();

        return response()->json(['status' => 'success']);
    }

    private function handleMercadoPagoSubscription(Request $request)
    {
        // $subscriptionId = $request->input('data.id');

        // $token = config('mercadopago.access_token');

        // $url = 'https://api.mercadopago.com/preapproval/' . $subscriptionId;

        // $response = Http::withToken($token)
        //     ->get($url);

        // if ($response->failed()) {
        //     return response()->json(['status' => 'error'], 500);
        // }

        return response()->json(['status' => 'not_implemented'], 500);
    }

    private function handleMercadoPagoSubscriptionPayment(Request $request)
    {
        $paymentId = $request->input('data.id');

        $token = config('mercadopago.access_token.ARG');

        $response = Http::withToken($token)
            ->get("https://api.mercadopago.com/authorized_payments/{$paymentId}");

        if ($response->failed()) {
            report(new \Exception('Webhook error' . $request->json()));
            return response()->json(['status' => 'error'], 500);
        }

        $response = Http::withToken($token)->get(
            "https://api.mercadopago.com/preapproval/" .
                $response->json("preapproval_id")
        );

        if ($response->failed()) {
            report(new \Exception('Webhook error' . $request->json()));
            return response()->json(['status' => 'error'], 500);
        }

        Candidate::findOrFail($response->json('external_reference'))
            ->payments()
            ->where('current_installment', $response->json('summarized.charged_quantity'))
            ->update([
                'status' => 'approved',
                'payment_id' => $paymentId,
                'paid_date' => $response->json('summarized.last_charged_date')
            ]);

        return $this->successResponse();
    }

    private function successResponse()
    {
        return response()->json(['status' => 'success']);
    }

    private function notImplementedResponse(Request $request)
    {
        report(new \Exception('Webhook not implemented' . $request->json()));
        return response()->json(['status' => 'not_implemented'], 501);
    }
}
