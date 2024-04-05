<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Payment;
use App\Services\Payment\StripePaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebHookPaymentsController extends Controller
{
    public function paypalWebhook(Request $request)
    {
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');
        $payment_id = $resource['id'];
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
            case 'PAYMENT.SALE.COMPLETED':
                if (Payment::where('payment_id', $payment_id)->first() != null) break; // payment exists

                $billing_agreement_id = $request->input('resource.billing_agreement_id');;
                $currentPayment = Payment::where('suscription_code', $billing_agreement_id)
                    ->where('status', 'pending')
                    ->orderBy('current_installment', 'ASC')
                    ->first();

                if ($currentPayment != null && $currentPayment->payment_id != null) // payment was processed
                    break;

                else if ($currentPayment  != null) {
                    $currentPayment->update(['status' => 'approved', 'payment_id' => $payment_id]);
                    Candidate::find($currentPayment->candidate_id)->update(['status' => UserStatus::Paying->value]);
                    if ($currentPayment->current_installment == $currentPayment->installment_number)
                        Candidate::find($currentPayment->candidate_id)->update(['status' => UserStatus::Paid->value]);
                    break;
                } else {
                    Log::error('currentInstallment not found');
                    break;
                }

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                Log::info('suscription activated', $request->all());
                break;

            default:
                break;
        }

        return response()->json(['status' => 'success']);
    }

    public function stripeWebhook(Request $request)
    {
        $stripePaymentMethod = new StripePaymentMethod();
        $stripePaymentMethod->processWebhook($request);

        return response()->json(['status' => 'success']);
    }

    private function successResponse()
    {
        return response()->json(['status' => 'success']);
    }

    private function notImplementedResponse(Request $request)
    {
        report(new \Exception('Webhook not implemented' . $request->json()));
        return response()->json(['status' => 'not_implemented'], 501);
    }
}
