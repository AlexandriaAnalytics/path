<?php

namespace App\Services\Payment\contracts;

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
}
