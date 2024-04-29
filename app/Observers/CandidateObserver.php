<?php

namespace App\Observers;

use App\Models\Candidate;
use App\Models\Payment;
use Carbon\Carbon;

class CandidateObserver
{
    /**
     * Handle the Candidate "created" event.
     */
    public function created(Candidate $candidate): void
    {
        //
    }

    /**
     * Handle the Candidate "updated" event.
     */
    public function updated(Candidate $candidate): void
    {
        dd($candidate->status);
        if ($candidate->status == 'unpaid' && $candidate->payments->contains('status', 'approved')) {
            $payment_deadline = $candidate->exams->min('payment_deadline');
            $candidate->installments = round(now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false), 0,) + 1;
            $payments = $candidate->payments;
            foreach ($payments as $payment) {
                $payment->status = 'cancelled';
                $payment->saveQuietly();
            }
            $candidate->saveQuietly();
        }
    }

    /**
     * Handle the Candidate "deleted" event.
     */
    public function deleted(Candidate $candidate): void
    {
        //
    }

    /**
     * Handle the Candidate "restored" event.
     */
    public function restored(Candidate $candidate): void
    {
        //
    }

    /**
     * Handle the Candidate "force deleted" event.
     */
    public function forceDeleted(Candidate $candidate): void
    {
        //
    }
}
