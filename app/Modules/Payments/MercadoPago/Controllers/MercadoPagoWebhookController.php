<?php

namespace App\Modules\Payments\MercadoPago\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    protected function handleSubscription(Request $request)
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

    protected function handleMercadoPagoSubscriptionPayment(Request $request)
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
