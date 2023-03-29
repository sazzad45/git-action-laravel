<?php


namespace App\Domain\FastPay\RequestHandler\Wallet\Transaction;


use App\Domain\FastPay\API\Transaction\Invoice;
use App\Domain\FastPay\FastPay;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceFetch
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $fp = new FastPay(auth()->user());
            return $fp->callAPI(new Invoice($request->invoice_id))->getResponse();
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
