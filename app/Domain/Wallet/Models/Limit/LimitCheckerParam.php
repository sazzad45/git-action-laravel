<?php


namespace App\Domain\Wallet\Models\Limit;


use App\Constant\LimitTypeId;
use App\Constant\UserAccountType;
use App\Domain\UserRelation\Models\User;

class LimitCheckerParam
{
    public ?Limit $dailyLimit = null;
    public ?Limit $monthlyLimit = null;
    public $amount;
    public User $user;

    public $transactionTypeId;
    public $userAccountTypeId;
    public $currencyId;
    public $level_id;
    public $user_type_id;
    public $isReceiver;

    public function __construct(
        User $user,
        $amount,
        $transactionTypeId,
        int $userAccountTypeId = UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
        int $currencyId = null,
        bool $isReceiver = false
    ){
        $this->user = $user;
        $this->isReceiver = $isReceiver;
        $this->amount = $amount;
        $this->transactionTypeId = $transactionTypeId;
        $this->userAccountTypeId = $userAccountTypeId;
        $this->currencyId = $currencyId == null ? config('basic_settings.currency_id') : $currencyId;
        $this->user_type_id = $this->user->user_type_id;

        $this->level_id = $user
                ->levels()
                ->wherePivot('status', 1)
                ->orderBy('level_users.created_at', 'DESC')
                ->first()
                ->pivot
                ->level_id ?? 1;

        $this->dailyLimit = $this->getLimitByType(LimitTypeId::DAILY);
        $this->monthlyLimit = $this->getLimitByType(LimitTypeId::MONTHLY);
    }

    public function getLimitByType(int $id)
    {
        $limit = Limit::where('status' ,1)
                        ->where('limit_type_id', $id)
                        ->where('currency_id', $this->currencyId)
                        ->where('user_account_type_id', $this->userAccountTypeId)
                        ->where('transaction_type_id', $this->transactionTypeId)
                        ->where('level_id', $this->level_id)
                        ->where('user_type_id', $this->user_type_id);

        if($limit->count())
        {
            return $limit->first();
        }

        return null;
    }

    public function getDailyLimitCrossedErrMsg(int $type)
    {
        if ($type == 1) {
            if ($this->isReceiver) {
                return trans('messages.recivers_daily_transaction_limit_crossed');
            }

            return trans('messages.your_daily_transaction_limit_crossed');
        }

        if ($this->isReceiver) {
            return trans('messages.recivers_daily_transaction_amount_limit_crossed');
        }

        return trans('messages.your_daily_transaction_amount_limit_crossed');
    }

    public function getMonthlyLimitCrossedErrMsg(int $type)
    {
        if ($type == 1) {
            if ($this->isReceiver) {
                return trans('messages.recivers_monthly_transaction_limit_crossed');
            }

            return trans('messages.your_monthly_transaction_limit_crossed');
        }

        if ($this->isReceiver) {
            return trans('messages.recivers_monthly_transaction_amount_limit_crossed');
        }

        return trans('messages.your_monthly_transaction_amount_limit_crossed');
    }
}
