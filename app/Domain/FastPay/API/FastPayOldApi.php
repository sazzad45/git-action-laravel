<?php


namespace App\Domain\FastPay\API;


interface FastPayOldApi
{
    public function call(string $token);
    public function getResponse();
}
