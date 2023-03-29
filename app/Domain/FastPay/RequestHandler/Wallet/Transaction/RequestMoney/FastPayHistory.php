<?php


namespace App\Domain\FastPay\RequestHandler\Wallet\Transaction\RequestMoney;

use App\Domain\FastPay\FastPay;
use App\Domain\FastPay\API\RequestMoney\History;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FastPayHistory
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $fp = new FastPay(auth()->user());

            return $fp->callAPI(new History([
                'lang' => app()->getLocale()
            ]))->getResponse();

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
