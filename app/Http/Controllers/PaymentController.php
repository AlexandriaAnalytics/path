<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodResult;
use App\Exceptions\PaymentException;
use App\Http\Requests\PaymentRequest;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\Contracts\IPaymentFactory;
use App\Services\Payment\PaymentFactory;
use Carbon\Carbon;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;


class PaymentController extends Controller
{

    private IPaymentFactory $paymentFactory;

    public function __construct(PaymentFactory $paymentFactory)
    {
        $this->paymentFactory = $paymentFactory;
    }

    public function createTransaction()
    {
        if (env('APP_ENV', 'local'))
            return view('welcome');
        else
            abort(403, 'Page not available');
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
                    $paymentMethod->setRedirectSuccess('https://success.com');
                    $paymentMethod->setRedirectCancel('https://success.com');
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

    public function processTransactionCuotas(PaymentRequest $request)
    {
        $validated = $request->validated();
        $candidate = Candidate::find(session('candidate')->id);

        try {
            $paymentMethod = $this->paymentFactory->create($validated['payment_method']);

            $paymentMethod->setRedirectSuccess(route('filament.candidate.pages.payments'));
            $paymentMethod->setRedirectCancel(route('payment.mp.cancel'));
            if ($validated['payment_method'] == 'paypal') { //TODO: sacar este horrible switch e implementar algun tipo de interfaz
                $paymentMethod->setRedirectSuccess(route('payment.paypal.success'));
                $paymentMethod->setRedirectCancel(route('payment.paypal.cancel'));
            }

            $paymentResult = $paymentMethod->suscribe(
                $candidate->id,
                $candidate->currency ?? 'USD',
                $candidate->total_amount,
                'Cuotas',
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
                'current_period' => Carbon::now()->day(1),
            ]);

            return 'Transaction complete.';
        } else {
            return  $response['message'] ?? 'Something went wrong.';
        }
    }

    /**
        @deprecated in the future this route action will be desapear
     */
    public function mpSuccessTransaction(Request $request)
    {
        return $request['message'] ?? 'success';
    }

    /**
        @deprecated in the future this route action will be reemplace by a generic view
     */
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
}
