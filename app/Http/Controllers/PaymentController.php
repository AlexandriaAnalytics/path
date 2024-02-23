<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use App\Http\Requests\PaymentRequest;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\Contracts\IPayment;
use App\Services\Payment\Contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use App\Services\Payment\StripePaymentMethod;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use LDAP\Result;
use MercadoPago\Client\MerchantOrder\MerchantOrderClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use App\Services\Payment\Contracts\AbstractPayment;


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

            switch ($validated['payment_method']) {
                case 'paypal':
                    $paymentMethod->setRedirectSuccess(route('payment.paypal.success'));
                    $paymentMethod->setRedirectCancel(route('payment.paypal.cancel'));
                    break;
                case 'mercado_pago':
                    $paymentMethod->setRedirectSuccess(route('payment.mp.success'));
                    $paymentMethod->setRedirectCancel(route('payment.mp.cancel'));
                    break;
            }

            $paymentResult = $paymentMethod->pay(
                $candidate->id,
                $candidate->student->name ?? 'DESCRIPTION',
                $candidate->currency ?? 'USD', //$candidate->student->region->monetary_unit,
                $candidate->total_amount,
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
    public function paypalSuccessTransaction(Request $request)
    {
        $provider = new PayPalClient;

        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $candidateId = $response['purchase_units'][0]['payments']['captures'][0]['custom_id'];
            $payer_id = $request->input('PayerID');

            Payment::create([
                'candidate_id' => $candidateId,
                'payment_method' => 'paypal',
                'payment_id' => $request->input('token'),
                'currency' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
                'amount' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
            ]);
            return 'Transaction complete.';
        } else {
            return  $response['message'] ?? 'Something went wrong.';
        }
    }

    public function mpSuccessTransaction(Request $request)
    {
        return $request['message'] ?? 'success';
    }

    public function mpCancelTransaction(Request $request)
    {
        return 'something went wrong';
    }



    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function paypalCancelTransaction(Request $request)
    {
        return $response['message'] ?? 'You have canceled the transaction.';
    }

    public function mercadopagoNotificationURL(Request $request)
    {
        Log::info('notification ' . $request->all());
        return Response::json(['message' => 'success']);
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

    public function processTransactionCuotas(PaymentRequest $request)
    {
        $validated = $request->validated();
        $candidate = Candidate::find(session('candidate')->id);

        try {
            $paymentMethod = $this->paymentFactory->create($validated['payment_method']);

            switch ($validated['payment_method']) { //TODO: sacar este horrible switch e implementar algun tipo de interfaz
                case 'paypal':
                    $paymentMethod->setRedirectSuccess(route('payment.paypal.success'));
                    $paymentMethod->setRedirectCancel(route('payment.paypal.cancel'));
                    break;
                case 'mercado_pago':
                    $paymentMethod->setRedirectSuccess(route('payment.mp.success'));
                    $paymentMethod->setRedirectCancel(route('payment.mp.cancel'));
                    break;
            }
            $paymentResult = $paymentMethod->suscribe(
                $candidate->id,
                'ARS',
                $candidate->total_amount,
                'pago en 3 cuotas',
                $request->input('cuotas')
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

 
    public function paypalWebhook(Request $request)
    {
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

            default:
                break;
        }

        return Response::json(['status' => 'success']);
    }


    /**
    @var 
    */  

    public function stripeWebhook(Request $request)
    {
        Log::info($request->all());
        
        $stripePaymentMethod = new StripePaymentMethod();
        $stripePaymentMethod->processWebhook($request);
        
        return Response::json([
            'status' => 'succes']);
    }
}
