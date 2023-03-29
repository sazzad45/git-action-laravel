<?php

namespace App\Traits;

use App\Constant\Currency;
use App\Constant\UserAccountType;
use App\Domain\Finance\Models\Commission;
use App\Domain\UserRelation\Models\User;

trait CommissionTrait
{
    protected function calculateCommission(
        User $sender,
        User $receiver,
        $amount,
        int $transactionTypeId,
        int $userAccountTypeId = UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
        $isMerchant = false
    )
    {

        $senderLevel = $sender
                ->levels()
                ->wherePivot('status', 1)
                ->orderBy('level_users.created_at', 'DESC')
                ->first()
                ->pivot
                ->level_id ?? 1;

        $charge = 0;
        $total = $amount + $charge;
        $commission = Commission::where('user_type_id', $sender->user_type_id)
            ->where('receiver_user_type_id', $receiver->user_type_id)
            ->where('level_id', $senderLevel)
            ->where('currency_id', Currency::IQD)
            ->where('transaction_type_id', $transactionTypeId)
            ->where('user_account_type_id', $userAccountTypeId)
            ->where('sender_charge', '>', 0)
            ->where('status', 1)
            ->where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->where(function($q) use ($receiver, $isMerchant) {
                if($isMerchant){
                    $q->where('merchant_id', $receiver->id);
                }
            });


        if ($commission->count() == 0) {
            return false;
        }

        $commission = $commission->first();

        return $commission;
    }

    protected function calculateCommissionOrCharge(
        User $sender,
        User $receiver,
        $amount,
        int $transactionTypeId,
        int $userAccountTypeId = UserAccountType::FASTPAY_SAVINGS_ACCOUNT,
        bool $onlyAmount = true,
        $isMerchant = false
    )
    {
        $senderLevel = $sender
                ->levels()
                ->wherePivot('status', 1)
                ->orderBy('level_users.created_at', 'DESC')
                ->first()
                ->pivot
                ->level_id ?? 1; // 1 is default level

        $charge = 0;
        $total = $amount + $charge;

        $commission = Commission::where('user_type_id', $sender->user_type_id)
            ->where('receiver_user_type_id', $receiver->user_type_id)
            ->where('level_id', $senderLevel)
            ->where('currency_id', Currency::IQD)
            ->where('transaction_type_id', $transactionTypeId)
            ->where('user_account_type_id', $userAccountTypeId)
            ->where('sender_charge', '>', 0)
            ->where('status', 1)
            ->where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->where(function($q) use ($receiver, $isMerchant) {
                if($isMerchant){
                    $q->where('merchant_id', $receiver->id);
                }
            });


        if ($commission->count() == 0) {
            return false;
        }

        $commission = $commission->first();

        if($onlyAmount == false)
        {
            return $commission;
        }

        $commissionAmount = 0;

        if ($commission->sender_slab_type == "F") {
            $commissionAmount = $commission->sender_charge;
        } else {
            $commissionAmount = ($amount * $commission->sender_charge)/100;
        }

        return [
            'amount' => ceil($commissionAmount)
        ];

    }


    protected function getCommissionAmount(Commission $commission, $amount)
    {
        $commissionAmount = 0;
        if ($commission->sender_slab_type == "F") {
            $commissionAmount = $commission->sender_charge;
        } else {
            $commissionAmount = ($amount * $commission->sender_charge)/100;
        }
        return ceil($commissionAmount);
    }

}
