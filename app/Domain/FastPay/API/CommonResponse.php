<?php


namespace App\Domain\FastPay\API;


trait CommonResponse
{
    private $fp_status_code = "";
    private $fp_message = "";
    private $fp_data = "";

    public function getCode()
    {
        return $this->fp_status_code;
    }

    public function getMessage()
    {
        return $this->fp_message;
    }

    public function getData()
    {
        return $this->fp_data;
    }
}
