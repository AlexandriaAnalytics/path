<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\AbstractPayment;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\PaymentResult;
use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripePaymentMethod extends AbstractPayment
{

    public function pay(string $id, string $description, string $currency, string $amount_value): PaymentResult
    {
        Stripe::setApiKey($this->getAccessToken());

        $stripe = new \Stripe\StripeClient($this->getAccessToken());
        $price = $stripe->prices->create([
            'currency' => 'USD', //$currency,
            'unit_amount' => round($amount_value) * 100,
            // 'recurring' => ['interval' => 'month'],
            'product_data' => ['name' => 'Path Exam'],
        ]);

        try {
            $session = Session::create([
                'success_url' => $this->getRedirectSuccess(),
                'cancel_url' => $this->getRedirectCancel(),
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'metadata' => [
                    'id' => $id
                ]
            ]);


            $candidate = Candidate::find($id);
            $candidate->status = UserStatus::Processing_payment->value;
            $candidate->save();

            Payment::create([
                'candidate_id' => $candidate->id,
                'payment_method' => 'stripe',
                'payment_id' => $session->id,
                'currency' => $currency,
                'amount' => round($amount_value),
            ]);

            return new PaymentResult(PaymentMethodResult::REDIRECT, null, $session->url);
        } catch (PaymentException $pe) {
            return new PaymentResult(
                PaymentMethodResult::ERROR,
                $pe->getMessage()
            );
        }
    }

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $instalment_number): PaymentResult
    {
        $amountPerInstalment = round($total_amount_value  / $instalment_number);

        Stripe::setApiKey($this->getAccessToken());

        $stripe = new \Stripe\StripeClient($this->getAccessToken());
        $price = $stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $amountPerInstalment,
            'recurring' => [
                'interval' => 'month',
            ],
            'product_data' => ['name' => 'Path Exam'],
        ]);


        try {
            $session = Session::create([
                //'success_url' => 'https://example.com/success',
                'success_url' => $this->getRedirectSuccess(),
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'subscription',
                'metadata' => [
                    'id' => $id
                ]
            ]);
            ray('session', $session);
            $candidate = Candidate::find($id);
            $candidate->update(['status', UserStatus::Processing_payment->value]);

            for ($instalment = 1; $instalment <= $instalment_number; $instalment++) {
                Payment::create([
                    'candidate_id' => $id,
                    'payment_method' => 'mercado_pago',
                    'currency' => $currency,
                    'amount' => $amountPerInstalment,
                    'suscription_code' => $session->id,
                    'instalment_number' => $instalment_number,
                    'current_instalment' => $instalment,
                    'status' => 'pending',
                ]);
            }

            Payment::create([
                'candidate_id' => $candidate->id,
                'payment_method' => 'stripe',
                'payment_id' => $session->id,
                'currency' => $currency,
                'amount' => $amountPerInstalment,
            ]);

            return new PaymentResult(PaymentMethodResult::REDIRECT, null, $session->url);
        } catch (PaymentException $pe) {
            return new PaymentResult(
                PaymentMethodResult::ERROR,
                $pe->getMessage()
            );
        }
    }

    public function processWebhook(Request $request)
    {
        $data = $request->input('data');
        $stripe = new StripeClient($this->getAccessToken());
        ray('stripe', $request);
        if ($request->input('type') == 'checkout.session.completed' && $data['object']['status'] == 'complete') {
            return match ($data['object']['mode']) {
                'payment' => $this->processPayment($data),
                'subscription' => $this->processSuscription($data),
                default => response()->json(['status' => 'not_implemented'], 501)
            };
        }
    }

    private function processPayment($data)
    {
        $payment = Payment::where('payment_id', $data['object']['id'])->first();
        if ($payment != null) {
            Log::info($payment);
            $payment->status = 'approved';
            $payment->save();

            $candidate = Candidate::find($payment->candidate_id);
            if ($candidate != null) {
                $candidate->status = UserStatus::Paid->value;
                Log::alert('Candidate state ' . $candidate->status);
                $candidate->save();
            }
        }
        return response()->json(['status' => 'ok'], 200);
    }

    private function processSuscription($data)
    {
        $candidateId = $data['object']['metadata']['id'];
        $installments = Payment::where('suscription_code', $data['object']['id']);

        $currentInstallment = $installments
            ->where('status', 'pending')
            ->orderBy('current_instalment', 'ASC')
            ->first();

        if($currentInstallment != null && $currentInstallment->payment_id != null)
            return response()->json(['status' => 'no current installment'], 500);

        if($currentInstallment != null){
            $currentInstallment->update(['status' => 'approved', 'payment_id' => 's-scr-'.$data['object']['id']]);
            Candidate::find($candidateId)->update(['status' => UserStatus::Paying->value]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function getAccessToken(): string
    {
        return config('stripe.access_token');
    }
}
