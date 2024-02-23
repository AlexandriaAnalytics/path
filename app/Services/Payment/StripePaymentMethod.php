<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\AbstractPayment;
use App\Models\Candidate;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use App\Services\Payment\PaymentResult;
use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Resources\Payment\PaymentMethodData;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripePaymentMethod extends AbstractPayment
{

    public function pay(string $id, string $description, string $currency, string $amount_value, string $mode = 'single'): PaymentResult
    {
        Stripe::setApiKey($this->getAccessToken());

        $stripe = new \Stripe\StripeClient($this->getAccessToken());
        $price = $stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $amount_value,
            // 'recurring' => ['interval' => 'month'],
            'product_data' => ['name' => 'Path Exam'],
        ]);

        try {
            $session = Session::create([
                'success_url' => 'https://example.com/success',
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
                'amount' => $amount_value,
            ]);

            return new PaymentResult(PaymentMethodResult::REDIRECT, null, $session->url);
        } catch (PaymentException $pe) {
            return new PaymentResult(
                PaymentMethodResult::ERROR,
                $pe->getMessage()
            );
        }
    }

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $instalment_number, string $mode = 'subscription'): PaymentResult
    {
        $amountPerInstalment = $total_amount_value  / $instalment_number;  

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
                'success_url' => 'https://example.com/success',
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

    private function getAccessToken(): string
    {
        return config('stripe.mode') === 'sandbox' ? config('stripe.sandbox.access_token') : config('stripe.live.access_token');
    }

    public function processWebhook(Request $request)
    {
        $data = $request->input('data');
        $type = $request->input('type');
        $stripe = new StripeClient($this->getAccessToken());
        Log::info('action' . $data['object']['object']);
        Log::info('id' . $data['object']['id']);
        switch ($type) {
            case 'checkout.session.completed':
                $sessionCompleted = $stripe->checkout->sessions->retrieve($data['object']['id']);
                if ($data['object']['status'] == 'complete') {
                    $payment = Payment::where('payment_id', $data['object']['id']);
                    $payment->status = 'approved';
                    $payment->save();

                    $candidate = Candidate::find($payment->candidate_id);
                    $candidate->status = UserStatus::Paid->value;
                    $candidate->save();
                }
                break;
        }
    }
}
