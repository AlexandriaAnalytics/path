<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\AbstractPayment;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\PaymentResult;
use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use Carbon\Carbon;
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
                'success_url' => 'https://www.google.com/', //$this->getRedirectSuccess(),
                //'cancel_url' => $this->getRedirectCancel(),
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


            $this->paymentService->createPayment($candidate->id, 'stripe', $session->id, $currency, $amount_value);

            return new PaymentResult(PaymentMethodResult::REDIRECT, null, $session->url);
        } catch (PaymentException $pe) {
            return new PaymentResult(
                PaymentMethodResult::ERROR,
                $pe->getMessage()
            );
        }
    }

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $installment_number): PaymentResult
    {
        $amountPerInstallment = round($total_amount_value  / $installment_number);

        Stripe::setApiKey($this->getAccessToken());

        $stripe = new \Stripe\StripeClient($this->getAccessToken());
        $price = $stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $amountPerInstallment,
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
            $candidate = Candidate::find($id);
            $candidate->update(['status', UserStatus::Processing_payment->value]);

            $this->createGroupOfInstallments($id, 'stripe', $currency, $amountPerInstallment, $session->id, $installment_number);


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
            ->orderBy('current_installment', 'ASC')
            ->first();

        if ($currentInstallment != null && $currentInstallment->payment_id != null)
            return response()->json(['status' => 'no current installment'], 500);

        if ($currentInstallment != null) {
            $currentInstallment->update(['status' => 'approved', 'payment_id' => 's-scr-' . $data['object']['id']]);
            Candidate::find($candidateId)->update(['status' => UserStatus::Paying->value]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function getAccessToken(): string
    {
        return config('stripe.access_token');
    }
}
