<?php

namespace App\Exceptions;

use Throwable;

class BookingFailedException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Sorry! Unable to process your purchase request. Please try again later.";

        parent::__construct($message, $code, $previous);
    }
}
