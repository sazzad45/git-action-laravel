<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\BundlePurchase;

use App\Constant\TransactionType;
use App\Constant\TransactionTypeCode;
use App\Constant\UserAccountType;
use App\Domain\FastPay\RequestHandler\Wallet\Transaction\BundlePurchase\FastPayPurchase;
use App\Domain\Independent\Models\Currency;
use App\Domain\Transaction\Library\BundlePurchase\BundleCard;
use App\Domain\Transaction\Library\BundlePurchase\BundlePurchaseRequestValidation;
use App\Domain\Transaction\Library\BundlePurchase\Param;
use App\Domain\Transaction\Library\TransactionGenerator;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Wallet\Models\Limit\LimitChecker;
use App\Domain\Wallet\Models\Limit\LimitCheckerParam;
use App\Domain\Wallet\Models\Limit\LimitUpdater;
use App\Exceptions\BookingFailedException;
use App\Exceptions\MerchantAccountNotMappedWithOperatorException;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\BundlePurchase\PurchaseRequest;
use App\Jobs\TransactionConsumeUpdate;
use App\Traits\CashBackTrait;
use App\Traits\CommissionTrait;
use App\Traits\RewardTrait;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseController extends APIBaseController
{
    use CommissionTrait;
    use TransactionTrait;
    use CashBackTrait;
    use RewardTrait;

    public function execute(PurchaseRequest $request)
    {
        try {
            return (new FastPayPurchase())->index($request);
        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->respondInJSON(500, [
                trans('messages.internal_server_error')
            ]);
        }
    }
}
