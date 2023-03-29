<?php

namespace App\Exceptions;

use Throwable;

class MerchantAccountNotMappedWithOperatorException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null, $operatorID)
    {
        // Consider this as a critical system error and send an email to corresponding department
        // to update the mapping in earliest possible time.

        $message = "Sorry! Operator & Merchant mapping is missing. Kindly try again later.";
        parent::__construct($message, $code, $previous);
    }
}
