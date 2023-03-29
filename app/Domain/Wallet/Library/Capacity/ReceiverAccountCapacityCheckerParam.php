<?php

namespace App\Domain\Wallet\Library\Capacity;

use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Models\Capacity\AccountCapacity;

class ReceiverAccountCapacityCheckerParam
{
    private User $receiverUser;
    private float $receiverCurrentBalance;
    private $trxAmount;
    private bool $actingUserIsReceiver;
    private ?AccountCapacity $receiverAccountCapacity;
    
    public function __construct(User $receiverUser, $trxAmount, bool $actingUserIsReceiver = false)
    {
        $this->receiverUser = $receiverUser;
        $this->receiverCurrentBalance = $this->getCurrentBalance($receiverUser);
        $this->trxAmount = $trxAmount;
        $this->actingUserIsReceiver = $actingUserIsReceiver;
        $this->receiverAccountCapacity = $this->getAccountCapacity($receiverUser, $this->getReceiverLevelId());
    }

    public function getReceiverUser()
    {
        return $this->receiverUser;
    }

    public function getReceiverCurrentBalance()
    {
        return (int) $this->receiverCurrentBalance;
    }

    public function getReceiverLevelId()
    {
        return $this->receiverUser->level->level_id ?? 1;
    }

    public function getTrxAmount()
    {
        return $this->trxAmount;
    }

    public function getReceiverAccountCapacity()
    {
        return $this->receiverAccountCapacity;
    }

    private function getCurrentBalance(User $user)
    {
        $account = UserAccount::where('user_id', $user->id)->first();
        return AccountBalance::where('user_account_id', $account->id)
            ->where('currency_id', config('basic_settings.currency_id'))
            ->first()
            ->balance;
    }

    private function getAccountCapacity(User $user, int $levelId)
    {
        return AccountCapacity::active()
            ->where('user_type_id', $user->user_type_id)
            ->where('level_id', $levelId)
            ->where('currency_id', config('basic_settings.currency_id'))
            ->first();
    }

    public function getErrorMsgForAccCapacityExceed()
    {
        if ($this->actingUserIsReceiver) {
            return trans('messages.your_account_capacity_exceeds');
        }

        return trans('messages.receivers_account_capacity_exceeds');
    }
}
