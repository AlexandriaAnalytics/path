<?php 

namespace App\Exceptions;

use Error;
use Exception;

class PaymentException extends Exception
{
    public const ERROR_INVALID_PAYMENT = 1;
    public const Error_PAYMENT_FAILED = 2;
    public const ERROR_PAYMENT_CANCELLED = 3;
    public const ERROR_INVALID_AMOUNT = 4;
    
    public function __construct($message = 'Payment failed', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}