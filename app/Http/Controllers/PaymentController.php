<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodResult;
use App\Enums\UserStatus;
use App\Exceptions\PaymentException;
use App\Http\Requests\PaymentRequest;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\Contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        Log::info('entro al area de pruebas');
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
            $paymentMethod->setRedirectSuccess(route('payment.paypal.success'));
            $paymentMethod->setRedirectCancel(route('payment.paypal.cancel'));

            $paymentResult = $paymentMethod->pay(
                $candidate->id,
                $candidate->student->names,
                'USD', //$candidate->student->region->monetary_unit,
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
    public function paypalSuccessTransaction(Request $request)
    {
        $provider = new PayPalClient;


        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
//Log::debug('paypal success transaction' . $response['purchase_units']['payments']['captures']['id']);
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
    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function paypalCancelTransaction(Request $request)
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

    public function processTransactionCuotas(Request $request)
    {
        $paymentMethod = $this->paymentFactory->create('paypal');
        $paymentMethod->setRedirectSuccess(route('payment.paypal.success'));
        $paymentMethod->setRedirectCancel(route('payment.paypal.cancel'));

        $paymentResult = $paymentMethod->suscribe(
            2, //session('candidate')->id,
            'USD',
            session('candidate')->total_amount,
            'pago en 3 cuotas',
            $request->input('cuotas')
        );

        if ($paymentResult->getResult() == PaymentMethodResult::REDIRECT) {
            return redirect()->away($paymentResult->getRedirectUrl());
        }
        if ($paymentResult->getResult() == PaymentMethodResult::ERROR) {
            return $paymentResult->getMessage(); //TODO: return error view
        }
    }

    
    public function paypalWebhook(Request $request)
    {
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');
        $payment_id = $resource['id'];
        Log::info('paypal webhook -> ' . $eventType .  ' id ' . $payment_id );
        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':

                Log::info('entre');
                $payment = Payment::where('payment_id', $resource['id'])->first();
                if($payment != null){
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
}
