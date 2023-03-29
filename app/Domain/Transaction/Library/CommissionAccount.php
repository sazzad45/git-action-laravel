<?php

namespace App\Domain\Transaction\Library;

use App\Constant\LevelAccountType;
use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\LevelAccount;
use App\Domain\UserRelation\Models\User;

class CommissionAccount
{
    private UserAccount $account;
    private AccountBalance $balanceAccountWithLock;

    public function __construct(User $sender)
    {
        $senderLevel = $sender->level->level_id ?? 1;
        $levelAccount  = LevelAccount::active()
            ->where('level_id', $senderLevel)
            ->where('type', LevelAccountType::CHARGE)
            ->latest('id')
            ->first();

        $this->account = UserAccount::whereId($levelAccount->user_account_id)->first();
        $this->balanceAccountWithLock = AccountBalance::where('user_account_id', $this->account->id)
            ->where('currency_id', config('basic_settings.currency_id'))->lockForUpdate()->first();
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getBalanceAccountWithLock()
    {
        return $this->balanceAccountWithLock;
    }
}
