<?php

namespace App\Exceptions\Transaction;

use Exception;
use Throwable;

class TransactionValidatorException extends Exception
{
    public function __construct($message = "Sorry! Something went wrong. Please try again later.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
