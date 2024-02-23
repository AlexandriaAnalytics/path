<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Models\Candidate;
use App\Services\Payment\Contracts\AbstractPayment;
use Exception;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentMethod extends AbstractPayment
{
    public function pay(string $id, string $description, string $currency, string $amount_value, $mode = 'single'): PaymentResult
    {

        if (!is_numeric($amount_value)) {
            return new PaymentResult(PaymentMethodResult::ERROR, 'Amount value must be a number');
        }

        if ($amount_value <= 0) {
            return new PaymentResult(PaymentMethodResult::ERROR, 'Amount value must be greater than 0');
        }
        /*
        if (strtoupper($currency) != 'USD') {
            return new PaymentResult(PaymentMethodResult::ERROR, 'Currency must be USD');
        }
        */

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken(); // TODO: this line can be removed just test if it works without it

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => $this->getRedirectSuccess(),
                "cancel_url" => $this->getRedirectCancel(),
            ],
            "purchase_units" => [
                [
                    "description" => $description,
                    "custom_id" => $id,
                    "amount" => [
                        "currency_code" => 'USD', //strtoupper($currency),
                        "value" => $amount_value,
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return new PaymentResult(PaymentMethodResult::REDIRECT, null, $links['href']);
                    // return redirect()->away($links['href']);
                }
            }
            return new PaymentResult(PaymentMethodResult::ERROR, 'Something went wrong.');
        } else {
            return new PaymentResult(PaymentMethodResult::ERROR, $response['message'] ?? 'Something went wrong.');
        }

        return new PaymentResult(PaymentMethodResult::SUCCESS, 'Payment was successful');
    }

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $instalment_number, string $mode = 'subscription'): PaymentResult
    {

        try {
            $amount = round($total_amount_value / $instalment_number, 2);

            $candidate = Candidate::find($id);
            if ($candidate == null) {
                return new PaymentResult(PaymentMethodResult::ERROR, 'Candidate not found');
            }

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $product = $provider->createProduct([
                'name' => 'Examen en 3 cuotas',
                'description' => 'Exam en 3 cuotas description',
                'type' => 'SERVICE', // Indica que el producto es un servicio
                'category' => 'EDUCATIONAL_AND_TEXTBOOKS',
                'image_url' => 'https://example.com/images/product-image.png',
                'home_url' => 'https://example.com/home',
            ]);



            $plan_request = [
                'product_id' => $product['id'], // Id del producto creado
                'name' => 'Plan de ' . $instalment_number . ' cuotas sin interes',
                'description' => 'Plan de ' . $instalment_number . ' cuotas sin interes',
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ],

                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => 'MONTH',
                            'interval_count' => 1
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => $instalment_number,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => $amount,
                                'currency_code' => 'USD', //$currency
                            ]
                        ]
                    ]
                ],
            ];


            $plan = $provider->createPlan($plan_request);


            $response = $provider->createSubscription([
                'plan_id' => $plan['id'],

                'application_context' => [
                    "shipping_preference" => "NO_SHIPPING",
                ],
            ]);

            if (isset($response['id']) && $response['id'] != null) {
                // redirect to approve href
                foreach ($response['links'] as $links)  {
                    if ($links['rel'] == 'approve') {
                        return new PaymentResult(PaymentMethodResult::REDIRECT, null, $links['href']);
                        // return redirect()->away($links['href']);
                    }
                }
                return new PaymentResult(PaymentMethodResult::ERROR, 'Something went wrong.');
            } else {
                return new PaymentResult(PaymentMethodResult::ERROR, $response['message'] ?? 'Something went wrong.');
            }

            if (isset($response['id']) && $response['id'] != null) {
                // redirect to approve href
                foreach ($response['links'] as $links) {
                    if ($links['rel'] == 'self' &&  $links['method'] == 'GET') {
                        return new PaymentResult(PaymentMethodResult::REDIRECT, null, $links['href']);
                        // return redirect()->away($links['href']);
                    }
                }
                if (isset($response['status']) && $response['status'] == 'APPROVED') {
                    return new PaymentResult(PaymentMethodResult::REDIRECT, null, $response['approve_link']);
                } else
                    return new PaymentResult(PaymentMethodResult::ERROR, 'Something went wrong.');
            } else {
                return new PaymentResult(PaymentMethodResult::ERROR, $response['message'] ?? 'Something went wrong.');
            }

            return new PaymentResult(PaymentMethodResult::SUCCESS, 'Payment was successful');
        } catch (Exception $e) {
            return new PaymentResult(PaymentMethodResult::ERROR, $e->getMessage());
        }
    }

    public function processWebhook(Request $request){
        // make logic
    }
}
