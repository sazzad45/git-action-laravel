<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\BundlePurchase;

use App\Domain\FastPay\RequestHandler\Wallet\Transaction\BundlePurchase\FastPayPurchase;
use App\Domain\FastPay\RequestHandler\Wallet\Transaction\BundlePurchase\FastPaySummary;
use App\Exceptions\MerchantAccountNotMappedWithOperatorException;
use App\Traits\CommissionTrait;
use App\Traits\TransactionTrait;
use App\Constant\TransactionType;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIBaseController;
use App\Domain\Transaction\Library\BundlePurchase\BundleCard;
use App\Http\Requests\API\Wallet\Transaction\BundlePurchase\SummaryRequest;

class SummaryController extends APIBaseController
{
    use CommissionTrait;
    use TransactionTrait;

    public function summary(SummaryRequest $request)
    {
        try {
            return (new FastPaySummary())->index($request);

        } catch (MerchantAccountNotMappedWithOperatorException $e) {
            return $this->respondInJSON(500, [$e->getMessage()]);
        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
