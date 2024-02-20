<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use App\Http\Requests\PaymentRequest;
use App\Models\Candidate;
use App\Services\Payment\Contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use MercadoPago\Client\MerchantOrder\MerchantOrderClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class PaymentController extends Controller
{

    private IPaymentFactory $paymentFactory;

    public function __construct(PaymentFactory $paymentFactory)
    {
        $this->paymentFactory = $paymentFactory;
    }


    public function createTransaction()
    {
        return view('welcome');
    }
    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(PaymentRequest $request)
    {

        $validated = $request->validated();
        try {
            $paymentMethod = $this->paymentFactory->create($validated['payment_method']);

            /** @var \App\Models\Candidate $candidate */
            $candidate = session('candidate');
            $paymentMethod->setRedirectSuccess(route('payment.success'));
            $paymentMethod->setRedirectCancel(route('payment.cancel'));

            $paymentResult = $paymentMethod->pay(
                $candidate->id,
                $candidate->student->names,
                $candidate->student->region->monetary_unit,
                round($candidate->total_amount),
            );

            if ($paymentResult->getResult() == PaymentMethodResult::REDIRECT) {
                return redirect()->away($paymentResult->getRedirectUrl());
            }
            if ($paymentResult->getResult() == PaymentMethodResult::ERROR) {
                return $paymentResult->getMessage(); //TODO: return error view
            }
        } catch (PaymentException $pe) {
            return $pe->getMessage(); //TODO: return error view
        }
    }
    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return 'Transaction complete.';
        } else {
            return  $response['message'] ?? 'Something went wrong.';
        }
    }
    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction(Request $request)
    {
        return $response['message'] ?? 'You have canceled the transaction.';
    }

    public function webhook(Request $request)
    {
        /*
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->verifyWebhook($request->getContent());
        if (isset($response['status']) && $response['status'] == 'VERIFIED') {
            return 'Webhook verified.';
        } else {
            return  $response['message'] ?? 'Webhook not verified.';
        }
        */
    }

    public function mercadoPagoWebhook(Request $request)
    {

        MercadoPagoConfig::setAccessToken(config('mercadopago.mode') === 'sandbox'
            ? config('mercadopago.sandbox.access_token')
            : config('mercadopago.live.access_token'));

        $id = $request->input('id');
        $paymentClient = new PaymentClient();
        $payment = $paymentClient->get($id);
        $orderClient = new MerchantOrderClient();
        $order = $orderClient->get($payment->order_id);

        // For each $order->items as $item
        foreach ($order->items as $item) {
            $candidate = Candidate::find($item->id);
            $candidate->status = UserStatus::Paid;
            $candidate->save();
        }

        return Response::json(['status' => 'success']);
    }
}
