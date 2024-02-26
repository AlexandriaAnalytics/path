<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\StripePaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class WebHookPaymentsController extends Controller
{
    public function paypalWebhook(Request $request)
    {
        Log::alert('data recibida ', $request->all());
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');
        $payment_id = $resource['id'];


        Log::info('paypal webhook -> ' . $eventType .  ' id ' . $payment_id);

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

                if(Payment::where('payment_id', $payment_id)->first() != null) break; // payment exists

                $billing_agreement_id = $request->input('resource.billing_agreement_id');;
                $currentPayment = Payment::where('suscription_code', $billing_agreement_id)
                    ->where('status', 'pending')
                    ->orderBy('current_instalment', 'ASC')
                    ->first();

                Log::info('suscription  paid', [$billing_agreement_id, $currentPayment]);

                if ($currentPayment != null && $currentPayment->payment_id != null) // payment was processed
                    break;


                else if ($currentPayment  != null) {
                    $currentPayment->update(['status' => 'approved', 'payment_id' => $payment_id]);
                    Candidate::find($currentPayment->candidate_id)->update(['status' => UserStatus::Paying->value]);
                    if ($currentPayment->current_instalment == $currentPayment->instalment_number)
                        Candidate::find($currentPayment->candidate_id)->update(['status' => UserStatus::Paid->value]);
                    break;
                }else {
                    Log::error('currentInstalment not found');
                    break;
                }



            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                Log::info('suscription activated', $request->all());
                break;

            default:
                break;
        }

        return Response::json(['status' => 'success']);
    }

    public function stripeWebhook(Request $request)
    {
        Log::info($request->all());

        $stripePaymentMethod = new StripePaymentMethod();
        $stripePaymentMethod->processWebhook($request);

        return Response::json([
            'status' => 'succes'
        ]);
    }

    public function mercadopagoWebhook(Request $request)
    {

        if ($request->input('action') == null) {
            return Response::json(['status' => 'do_nothing']);
        }

        if ($request->input('action') == 'payment.created') {

            $orderId = $request->input('data.id');

            $token = config('mercadopago.mode') == 'sandbox' ? config('mercadopago.sandbox.access_token') : config('mercadopago.live.access_token');


            $headers = [
                'Authorization' => 'Bearer ' . $token,
            ];

            $url = 'https://api.mercadopago.com/v1/payments/' . $orderId;

            $response = Http::withToken($token)->get($url);

            if ($response->successful()) {
                $result = $response->body();
                $data = json_decode($result, true);

                $candidateId = $data['external_reference'];

                Payment::create([
                    'candidate_id' => $candidateId,
                    'payment_method' => 'mercado_pago',
                    'payment_id' => $orderId,
                    'currency' => $data['currency_id'],
                    'amount' => $data['additional_info']['items'][0]['unit_price'],
                    'status' => 'approved',
                ]);

                $candidate = Candidate::find($candidateId);
                $candidate->status = 'paid';
                $candidate->save();
            }

            return Response::json(['status' => 'success']);
        }
    }
}
