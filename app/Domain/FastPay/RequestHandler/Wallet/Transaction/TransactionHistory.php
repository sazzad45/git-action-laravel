<?php


namespace App\Domain\FastPay\RequestHandler\Wallet\Transaction;

use Illuminate\Http\Request;
use App\Domain\FastPay\FastPay;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Domain\FastPay\API\Transaction\TransactionList;

class TransactionHistory
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $fp = new FastPay(auth()->user());
            return $fp->callAPI(new TransactionList())->getResponse();
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
