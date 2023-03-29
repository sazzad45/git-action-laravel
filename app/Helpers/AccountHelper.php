<?php

namespace App\Helpers;

use App\Constant\LevelConst;
use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Level\Models\LevelUser;
use App\Domain\UserRelation\Models\User;

final class AccountHelper
{
    public static function getLevelIdByUser(User $user): int
    {
        return LevelUser::whereUserId($user->id)->latest('id')->first()->level_id ?? LevelConst::TEMPORARY_ACCOUNT;
    }

    public static function getUserAccount($key, $val): ?UserAccount
    {
        return UserAccount::where($key, $val)->first();
    }

    public static function getUserBalanceAccountByAccountIdWithLock(int $userAccountId, int $currencyId): ?AccountBalance
    {
        return AccountBalance::whereUserAccountId($userAccountId)
            ->whereCurrencyId($currencyId)
            ->lockForUpdate()
            ->first();
    }
}
