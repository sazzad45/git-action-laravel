<?php

namespace App\Domain\FastPay\RequestHandler\User;

use App\Domain\FastPay\API\User\BalanceInfo;
use App\Domain\FastPay\FastPay;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;

class UserBalanceInfo
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $fp = new FastPay(auth()->user());
            $fp->callAPI(new BalanceInfo());
            $data = $fp->getData();

            if (isset($data['currency']) && isset($data['balance'])) {
                $data['old_balance'] = true;
                return $data;
            }

            return [
                "account_type" => "",
                "account_no" => "",
                "currency" => "IQD",
                "balance" => "0"
            ];
        }catch (\Exception $exception){

            Log::error($exception->getFile() . ' ' . $exception->getLine() . ' ' . $exception->getMessage());
            Log::error($exception);

            if($exception == 401){
                throw new \Exception('Unauthenticated',401);
            }
        }
    }
}
