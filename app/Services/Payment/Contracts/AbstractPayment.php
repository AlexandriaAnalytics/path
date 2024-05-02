<?php

namespace App\Services\Payment\Contracts;

use App\Models\Payment;
use Carbon\Carbon;

abstract class AbstractPayment implements IPayment
{

     private string $redirectSuccess = 'https://sinapsis.pathexaminations.com/candidate/';
     private string $redirectCancel = 'https://sinapsis.pathexaminations.com/candidate/';


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

     protected function createGroupOfInstallments($id, $paymentMethod, $currency, $amountPerMonth, $suscriptionCode, $installmentNumber)
     {
          $currentDate = Carbon::now()->day(1);

          for ($installment = 1; $installment <= $installmentNumber; $installment++)
               Payment::create([
                    'candidate_id' => $id,
                    'payment_method' => $paymentMethod,
                    'currency' => $currency,
                    'amount' => $amountPerMonth,
                    'suscription_code' => $suscriptionCode,
                    'installment_number' => $installmentNumber,
                    'current_installment' => $installment,
                    'status' => 'pending',
                    'expiration_date' => $currentDate,
                    'current_period' => $currentDate->addMonth(),
               ]);
     }
}
