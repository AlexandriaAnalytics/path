<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\Contracts\AbstractPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentMethod extends AbstractPayment
{
    private const AVAILABLE_CURRENCIES = [ // https://developer.paypal.com/docs/reports/reference/paypal-supported-currencies/
        'AUD',
        'BRL',
        'CAD',
        'CNY',
        'CZK',
        'DDK',
        'EUR',
        'HKD',
        'HUF',
        'ILS',
        'JPY',
        'MYR',
        'MXN',
        'TWD',
        'NZD',
        'NOK',
        'PHP',
        'PLN',
        'GBP',
        'SGD',
        'SEK',
        'CHF',
        'THB',
        'USD'
    ];

    public function pay(string $id, string $description, string $currency, string $amount_value): PaymentResult
    {
        if (!is_numeric($amount_value))
            return new PaymentResult(PaymentMethodResult::ERROR, 'Amount value must be a number');

        if ($amount_value <= 0)
            return new PaymentResult(PaymentMethodResult::ERROR, 'Amount value must be greater than 0');

        /*
        if (strtoupper($currency) != 'USD')
            return new PaymentResult(PaymentMethodResult::ERROR, 'Currency must be USD');
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
                }
            }
            return new PaymentResult(PaymentMethodResult::ERROR, 'Something went wrong in paypal payment.');
        } else {
            return new PaymentResult(PaymentMethodResult::ERROR, $response['message'] ?? 'Something went wrong.');
        }

        return new PaymentResult(PaymentMethodResult::SUCCESS, 'Payment was successful');
    }

    public function suscribe(string $id, string $currency, string $total_amount_value, string $description, int $installment_number): PaymentResult
    {
        $currency = 'USD';
        if (!in_array($currency, PaypalPaymentMethod::AVAILABLE_CURRENCIES))
            throw new PaymentException('Currency not supported');

        try {
            $amount = round(floatval($total_amount_value) / $installment_number, 2);

            $candidate = Candidate::find($id);
            if ($candidate == null)
                return new PaymentResult(PaymentMethodResult::ERROR, 'Candidate not found');

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $product = $provider->createProduct([
                'name' => 'Examen en 3 cuotas',
                'description' => 'Exam en 3 cuotas description',
                'type' => 'SERVICE', // type of product see paypal docuumentation
                'category' => 'EDUCATIONAL_AND_TEXTBOOKS', // type of category provided for paypal api see documentation  
                'image_url' => 'https://example.com/images/product-image.png',
                'home_url' => 'https://example.com/home',
            ]);



            $plan_request = [
                'product_id' => $product['id'], // Id del producto creado
                'name' => 'Plan de ' . $installment_number . ' cuotas sin interes',
                'description' => 'Plan de ' . $installment_number . ' cuotas sin interes',
                'plan_request' => ['id' => $id],
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
                        'total_cycles' => $installment_number,
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

                foreach ($response['links'] as $links) {
                    if ($links['rel'] == 'approve') {
                        $this->createGroupOfInstallments($id, 'paypal', $currency, $amount, $response['id'], $installment_number);

                        Candidate::where('id', $id)->update(['status' => UserStatus::Processing_payment]);
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

    public function processWebhook(Request $request)
    {
        // make magic logic
    }
}
