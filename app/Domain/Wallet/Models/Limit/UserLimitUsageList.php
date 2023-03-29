<?php


namespace App\Domain\Wallet\Models\Limit;

use App\Constant\LimitTypeId;
use App\Domain\UserRelation\Models\User;
use Illuminate\Support\Facades\DB;
use App\Constant\UserAccountType;

class UserLimitUsageList
{
    private User $user;
    private array $responseOutput;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->responseOutput = $this->getResponse();
    }

    public function get(): array
    {
        return $this->responseOutput;
    }

    private function getUserLevel(): int
    {
        $userLevel = DB::table('level_users')
                        ->where('user_id', $this->user->id)
                        ->where('status', 1)
                        ->orderBy('created_at', 'DESC')
                        ->first();

        if($userLevel != ""){
            return $userLevel->level_id;
        }

        return 1;
    }

    private function getResponse(): array
    {
        $limits = Limit::where('level_id', $this->getUserLevel())
            ->where('currency_id', config('basic_settings.currency_id'))
            ->where('user_account_type_id', UserAccountType::FASTPAY_SAVINGS_ACCOUNT)
            ->where('user_type_id', $this->user->user_type_id)
            ->where('status', 1)
            ->cursor();

        $responseListDaily = [];
        $responseListMonthly = [];

        foreach ($limits as $limit) {

            $servieName = $limit->transactionType->name ?? '';

            if ($limit->limit_type_id == LimitTypeId::DAILY) {

                $consumed = $this->dailyConsumed($this->user, $limit);

                $responseListDaily[] = [
                    'service_type' => $servieName,
                    'total_max_no_of_tx' => number_format((int)$limit->max_number_of_tx),
                    'total_max_amount' => number_format((int)$limit->max_tx_amount),
                    'consumed_tx_count' => number_format((int)$consumed['consumed_tx_count']),
                    'consumed_tx_amount' => number_format((int)$consumed['consumed_tx_amount'])
                ];
            } else {

                $consumed = $this->monthlyConsumed($this->user, $limit);

                $responseListMonthly[] = [
                    'service_type' => $servieName,
                    'total_max_no_of_tx' => number_format((int)$limit->max_number_of_tx),
                    'total_max_amount' => number_format((int)$limit->max_tx_amount),
                    'consumed_tx_count' => number_format((int)$consumed['consumed_tx_count']),
                    'consumed_tx_amount' => number_format((int)$consumed['consumed_tx_amount'])
                ];
            }

        }

        return [
            'daily' => $responseListDaily,
            'monthly' => $responseListMonthly
        ];
    }

    private function dailyConsumed(User $user, Limit $limit): array
    {
        $limitIds = $this->getLimitIdsByType(LimitTypeId::DAILY, $limit);
        $consumption = UserLimit::where('user_id', $user->id)
            ->whereIn('daily_limit_id', $limitIds)
            ->where('action_date', date('Y-m-d'))
            ->selectRaw('SUM(number_of_tx) AS total_trx_consume, SUM(tx_amount) AS total_amount_consume')
            ->groupBy('user_id')
            ->first();

        return [
            'consumed_tx_count' => $consumption->total_trx_consume ?? 0,
            'consumed_tx_amount' => $consumption->total_amount_consume ?? 0
        ];
    }


    private function monthlyConsumed(User $user, Limit $limit): array
    {
        $limitIds = $this->getLimitIdsByType(LimitTypeId::MONTHLY, $limit);
        $consumption = UserMonthlyLimit::where('user_id', $user->id)
            ->whereIn('monthly_limit_id', $limitIds)
            ->where('action_month', date('m'))
            ->where('action_year', date('Y'))
            ->selectRaw('SUM(number_of_tx) AS total_trx_consume, SUM(tx_amount) AS total_amount_consume')
            ->groupBy('user_id')
            ->first();

        return [
            'consumed_tx_count' => $consumption->total_trx_consume ?? 0,
            'consumed_tx_amount' => $consumption->total_amount_consume ?? 0
        ];
    }

    private function getLimitIdsByType(int $limitTypeId, Limit $limit)
    {
        $limitIds = Limit::where('limit_type_id', $limitTypeId)
            ->where('currency_id', $limit->currency_id)
            ->where('user_account_type_id', $limit->user_account_type_id)
            ->where('transaction_type_id', $limit->transaction_type_id)
            ->where('user_type_id', $limit->user_type_id)
            ->pluck('id');

        return $limitIds;
    }
}
