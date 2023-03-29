<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\BundlePurchase;

use App\Domain\FastPay\RequestHandler\Wallet\Transaction\BundlePurchase\FastPayGetBundleList;
use App\Domain\FastPay\RequestHandler\Wallet\Transaction\BundlePurchase\FastPayGetOperator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\BundlePurchase\FetchOperatorRequest;

class OperatorController extends APIBaseController
{
    public function index(FetchOperatorRequest $request)
    {
        try {
            return (new FastPayGetOperator())->index($request);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
