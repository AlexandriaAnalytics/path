<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodResult;
use App\Exceptions\PaymentException;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;

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
        try{
            $paymentMethod = $this->paymentFactory->create($validated['payment_method']);
    
            $paymentMethod->setRedirectSuccess(route('payment.success'));
            $paymentMethod->setRedirectCancel(route('payment.cancel'));
            
            $paymentResult = $paymentMethod->pay((float) $validated['amount']);
    
            if($paymentResult->getResult() == PaymentMethodResult::REDIRECT){
                return redirect()->away($paymentResult->getRedirectUrl());
            }
            if($paymentResult->getResult() == PaymentMethodResult::ERROR){
                return $paymentResult->getMessage(); //TODO: return error view
            }

        }catch(PaymentException $pe){
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
}
