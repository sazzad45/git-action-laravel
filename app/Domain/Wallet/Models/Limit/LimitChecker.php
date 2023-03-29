<?php

namespace App\Domain\Wallet\Models\Limit;

use App\Constant\LimitTypeId;

class LimitChecker
{
    private $hasErrors;
    private $errorMessage = array();
    private LimitCheckerParam $param;

    public function __construct(LimitCheckerParam $param)
    {
        $this->hasErrors = false;
        $this->param = $param;
    }

    private function processStart()
    {
        if ($this->param->dailyLimit != null) {
            $this->checkDailyLimitCrossed($this->getUserLimitForDaily($this->param->dailyLimit->id));
        }

        if ($this->param->monthlyLimit != null) {
            $this->checkMonthlyLimitCrossed($this->getUserLimitForMonthly($this->param->monthlyLimit->id));
        }

    }


    private function getUserLimitForDaily($dailyLimitId)
    {
        $limit = UserLimit::where('user_id', $this->param->user->id)
            ->where('action_date', date('Y-m-d'))
            ->where('daily_limit_id', $dailyLimitId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($limit != "") {
            return $limit;
        }

        $newUserLimit = new UserLimit();
        $newUserLimit->action_date = date('Y-m-d');
        $newUserLimit->user_id = $this->param->user->id;
        $newUserLimit->daily_limit_id = $dailyLimitId;

        $newUserLimit->number_of_tx = 0;
        $newUserLimit->tx_amount = 0;

        $newUserLimit->created_at = date('Y-m-d H:i:s');
        $newUserLimit->updated_at = date('Y-m-d H:i:s');

        $newUserLimit->save();
        return $newUserLimit;
    }

    private function getUserLimitForMonthly($monthlyLimitId)
    {
        $thisMonth = date('m');
        $thisYear = date('Y');

        $monthlyLimit = UserMonthlyLimit::where('user_id', $this->param->user->id)
            ->where('action_month', $thisMonth)
            ->where('action_year', $thisYear)
            ->where('monthly_limit_id', $monthlyLimitId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($monthlyLimit != "") return $monthlyLimit;

        $newUserLimit = new UserMonthlyLimit();
        $newUserLimit->action_month = $thisMonth;
        $newUserLimit->action_year = $thisYear;
        $newUserLimit->user_id = $this->param->user->id;
        $newUserLimit->monthly_limit_id = $monthlyLimitId;
        $newUserLimit->number_of_tx = 0;
        $newUserLimit->tx_amount = 0;
        $newUserLimit->created_at = date('Y-m-d H:i:s');
        $newUserLimit->updated_at = date('Y-m-d H:i:s');
        $newUserLimit->save();

        return $newUserLimit;
    }

    private function getUserTotalConsumptionForDaily(UserLimit $userLimit) : array
    {
        $limitIds = $this->getLimitIdsByType(LimitTypeId::DAILY, [$userLimit->daily_limit_id]);

        $consumption = UserLimit::where('id', '!=', $userLimit->id)
            ->where('user_id', $userLimit->user_id)
            ->whereIn('daily_limit_id', $limitIds)
            ->where('action_date', $userLimit->action_date)
            ->selectRaw('SUM(number_of_tx) AS total_trx_consume, SUM(tx_amount) AS total_amount_consume')
            ->groupBy('user_id')
            ->first();

        return [
            'total_trx_consume' => doubleval($consumption->total_trx_consume ?? 0),
            'total_amount_consume' => doubleval($consumption->total_amount_consume ?? 0),
        ];
    }

    private function getUserTotalConsumptionForMonthly(UserMonthlyLimit $userLimit) : array
    {
        $limitIds = $this->getLimitIdsByType(LimitTypeId::MONTHLY, [$userLimit->monthly_limit_id]);

        $consumption = UserMonthlyLimit::where('id', '!=', $userLimit->id)
            ->where('user_id', $userLimit->user_id)
            ->whereIn('monthly_limit_id', $limitIds)
            ->where('action_month', $userLimit->action_month)
            ->where('action_year', $userLimit->action_year)
            ->selectRaw('SUM(number_of_tx) AS total_trx_consume, SUM(tx_amount) AS total_amount_consume')
            ->groupBy('user_id')
            ->first();

        return [
            'total_trx_consume' => doubleval($consumption->total_trx_consume ?? 0),
            'total_amount_consume' => doubleval($consumption->total_amount_consume ?? 0),
        ];
    }

    private function getLimitIdsByType(int $id, array $ignoreLimitIds = [])
    {
        $limitIds = Limit::where('limit_type_id', $id)
            ->where('currency_id', $this->param->currencyId)
            ->where('user_account_type_id', $this->param->userAccountTypeId)
            ->where('transaction_type_id', $this->param->transactionTypeId)
            ->where('user_type_id', $this->param->user_type_id);

        if ($ignoreLimitIds) {
            $limitIds->whereNotIn('id', $ignoreLimitIds);
        }
        
        $limitIds = $limitIds->pluck('id');

        return $limitIds;
    }

    private function checkDailyLimitCrossed(UserLimit $userLimit)
    {
        $dailyConsumption = $this->getUserTotalConsumptionForDaily($userLimit);

        $totalNumberOfTrx = ($userLimit->number_of_tx + 1) + $dailyConsumption['total_trx_consume'];
        $totalTrxAmount = ($userLimit->tx_amount + $this->param->amount) + $dailyConsumption['total_amount_consume'];

        if ($totalNumberOfTrx > $this->param->dailyLimit->max_number_of_tx) {
            $this->hasErrors = true;
            $this->errorMessage[] = $this->param->getDailyLimitCrossedErrMsg(1);
        }

        if ($totalTrxAmount > $this->param->dailyLimit->max_tx_amount) {
            $this->hasErrors = true;
            $this->errorMessage[] = $this->param->getDailyLimitCrossedErrMsg(2);
        }

    }


    private function checkMonthlyLimitCrossed(UserMonthlyLimit $userLimit)
    {
        $monthlyConsumption = $this->getUserTotalConsumptionForMonthly($userLimit);

        $totalNumberOfTrx = ($userLimit->number_of_tx + 1) + $monthlyConsumption['total_trx_consume'];
        $totalTrxAmount = ($userLimit->tx_amount + $this->param->amount) + $monthlyConsumption['total_amount_consume'];

        if ($totalNumberOfTrx > $this->param->monthlyLimit->max_number_of_tx) {
            $this->hasErrors = true;
            $this->errorMessage[] = $this->param->getMonthlyLimitCrossedErrMsg(1);
        }

        if ($totalTrxAmount > $this->param->monthlyLimit->max_tx_amount) {
            $this->hasErrors = true;
            $this->errorMessage[] = $this->param->getMonthlyLimitCrossedErrMsg(2);
        }
    }

    public function check()
    {
        $this->processStart();
        return $this;
    }

    public function limitCrossed()
    {
        if ($this->hasErrors) {
            return response()->json([
                'code' => 422,
                'messages' => $this->errorMessage,
                'data' => null
            ], 200);
        }

        return false;
    }
}
