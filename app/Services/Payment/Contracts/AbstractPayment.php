<?php

namespace App\Services\Payment\Contracts;

use App\Models\Payment;
use App\Services\Payment\PaymentResourceService;
use Carbon\Carbon;

abstract class AbstractPayment implements IPayment
{

     private string $redirectSuccess = '/';
     private string $redirectCancel = '/';


     public function setRedirectSuccess(string $url): void
     {
          $this->redirectSuccess = $url;
     }

     public function setRedirectCancel(string $url): void
     {
          $this->redirectCancel = $url;
     }

     public function getRedirectSuccess(): string
     {
          return $this->redirectSuccess;
     }

     public function getRedirectCancel(): string
     {
          return $this->redirectCancel;
     }

     protected function createGroupOfInstallments($id, $paymentMethod, $currency, $amountPerMonth, $suscriptionCode, $installmentNumber) {
          $currentDate = Carbon::now()->day(1);

          for ($instalment = 1; $instalment <= $installmentNumber; $instalment++)
               Payment::create([
                         'candidate_id' => $id,
                         'payment_method' => $paymentMethod,
                         'currency' => $currency,
                         'amount' => $amountPerMonth,
                         'suscription_code' => $suscriptionCode,
                         'instalment_number' => $installmentNumber,
                         'current_instalment' => $instalment,
                         'status' => 'pending',
                         'expiration_date'=> $currentDate,
                         'current_period' => $currentDate->addMonth(),
                    ]);
     }
}
