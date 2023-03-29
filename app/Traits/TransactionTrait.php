<?php

namespace App\Traits;

use App\Channels\FcmPushChannel;
use App\Constant\TransactionTypeCode;
use App\Constant\UserAccountType;
use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\UserRelation\Models\User;



trait TransactionTrait
{
    private function fetchUserObject(string $mobileNumber)
    {
        return User::where('mobile_no', '=', $mobileNumber)
            ->with(
                'userType',
                'userStatus',
                'accounts.accountBalances.currency',
                'accounts.userAccountType',
                'levels'
            )
            ->first();
    }

    public function getFastLinkGLUserObject(string $mobileNumber)
    {
        return User::where('mobile_no', '=', $mobileNumber)
            ->with(
                'userType',
                'userStatus',
                'accounts.accountBalances.currency',
                'accounts.userAccountType'
            )->first();
    }

    public function getOperatorMerchantUserObject(string $mobileNumber)
    {
        return User::where('mobile_no', '=', $mobileNumber)
            ->with(
                'userType',
                'userStatus',
                'accounts.accountBalances.currency',
                'accounts.userAccountType')
            ->first();
    }

    private function fetchUserByKeyVal($key, $val)
    {
        return User::where($key, $val)
            ->with(
                'userType',
                'userStatus',
                'accounts.accountBalances.currency',
                'accounts.userAccountType',
                'levels'
            )
            ->first();
    }

    private function fetchAccountByKeyVal($key, $val, $accountType = UserAccountType::FASTPAY_SAVINGS_ACCOUNT)
    {
        return UserAccount::with('user')->where($key, $val)
            ->where('user_account_type_id', $accountType)
            ->first();
    }

    private function fetchBalanceAccountWithLock($accountId, $currencyId)
    {
        return AccountBalance::where('user_account_id', $accountId)->where('currency_id', $currencyId)
            ->lockForUpdate()->first();
    }

    protected function notify($transactionTypeCode, ?User $sender, ?User $receiver, $response, $action_url = '')
    {
        try{

            /* if($transactionTypeCode == TransactionTypeCode::BUNDLE_PURCHASE)
            {
                $sender->notify(
                    new \App\Notifications\Transactions\BundlePurchase\Received(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Receiver')
                    )
                );
            }

            if($transactionTypeCode == TransactionTypeCode::DEPOSIT_MONEY)
            {
                $receiver->notify(
                    new \App\Notifications\Transactions\DepositMoney\Received(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Receiver')
                    )
                );
            }

            if($transactionTypeCode == TransactionTypeCode::PAYMENT)
            {
                $sender->notify(
                    new \App\Notifications\Transactions\MerchantPayment\Sent(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Sender')
                    )
                );
                $receiver->notify(
                    new \App\Notifications\Transactions\MerchantPayment\Received(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Receiver')
                    )
                );
            }

            if($transactionTypeCode == TransactionTypeCode::SEND_MONEY)
            {
                $sender->notify(
                    new \App\Notifications\Transactions\SendMoney\Sent(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Sender')
                    )
                );

                $receiver->notify(
                    new \App\Notifications\Transactions\SendMoney\Received(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Receiver')
                    )
                );
            }

            if($transactionTypeCode == TransactionTypeCode::WITHDRAW_MONEY)
            {
                $sender->notify(
                    new \App\Notifications\Transactions\WithdrawMoney\Sent(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Sender')
                    )
                );

                $receiver->notify(
                    new \App\Notifications\Transactions\WithdrawMoney\Received(
                        ['mail', 'database', FcmPushChannel::class],
                        $response->notificationMessage('Receiver')
                    )
                );
            } */

            if($transactionTypeCode == TransactionTypeCode::REQUEST_MONEY)
            {
                $receiver->notify(
                    new \App\Notifications\Transactions\RequestMoney\RequestReceived(
                        ['mail', 'database', FcmPushChannel::class],
                        'You have a new money request',
                        $action_url
                    )
                );
            }


        }catch (\Exception $e){
            \Log::error($e);
        }
    }
}
