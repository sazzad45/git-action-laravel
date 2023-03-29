<?php

namespace App\Domain\FastPay\RequestHandler\Wallet\Transaction\BundlePurchase;

use App\Domain\FastPay\API\BundlePurchase\Purchase;
use App\Domain\FastPay\FastPay;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FastPayPurchase
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $fp = new FastPay(auth()->user());

            return $fp->callAPI(new Purchase([
                'operator_id' => $request->operator_id,
                'type' => $request->type,
                'recharge_plan_id' => $request->bundle_id,
                'check' => 1,
                'lang' => app()->getLocale()
            ]))->getResponse();

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
