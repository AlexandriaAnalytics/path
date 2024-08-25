<?php

namespace App\Http\Controllers;

use App\Models\OtherPayment;
use App\Models\OtherPaymentDetail;
use Illuminate\Http\Request;

class OtherPaymentDetailController extends Controller
{
    public function destroy($id)
    {
        $paymentDetail = OtherPaymentDetail::findOrFail($id);
        $otherPayment = OtherPayment::find($paymentDetail->other_payment_id);
        $otherPayment->amount_paid = $otherPayment->amount_paid - $paymentDetail->amount;
        $otherPayment->save();
        $paymentDetail->delete();

        return redirect()->back()->with('success', 'Record deleted successfully');
    }
}
