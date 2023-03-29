<?php


namespace App\Domain\FastPay\RequestHandler\Wallet\Transaction\CashIn;

use App\Domain\FastPay\API\CashIn\ExecuteStep2;
use App\Domain\FastPay\FastPay;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FastPayCashInExecution
{
    use ApiResponseTrait;

    public function execute(Request $request)
    {
        try {
            $fp = new FastPay(auth()->user());
            return $fp->callAPI(new ExecuteStep2([
                'lang' => app()->getLocale(),
                'receiver_mobile_number' => $request->receiver_mobile_number,
                'amount' => $request->amount,
                'check' => $request->check ?? '1',
                'pin' => $request->pin
            ]))->getResponse();

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
