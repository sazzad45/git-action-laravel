<?php

namespace App\Traits;

use App\Constant\UserStatusId;
use App\Constant\UserTypeId;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\UserRelation\Models\User;
use App\Mail\TriedToSendDuplicateTransferWithinTimeLimit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait ValidationHelperTrait
{
    use ApiResponseTrait;
    use DuplicateTransactionCheckerTrait;
    use HelperTrait;
    use FeatureAccessTrait;

    public function isSenderOfType($sender, $type)
    {
        if ($sender->userType->name != $type)
            return $this->invalidResponse([trans("messages.sender_account_must_be_" . strtolower($type))]);
        else
            return false;
    }

    public function isReceiverOfType($receiver, $type)
    {
        if ($receiver->userType->name != $type)
            return $this->invalidResponse([trans('messages.receiver_account_must_be_' . strtolower($type))]);
        else
            return false;
    }

    public function isSenderPersonal($sender)
    {
        if ($sender->userType->name != "Personal")
            return $this->invalidResponse([trans('messages.sender_account_must_be_personal')]);
        else
            return false;
    }

    public function isSenderAgent($sender)
    {
        if ($sender->user_type_id != UserTypeId::AGENT)
            return $this->invalidResponse([trans('messages.sender_account_must_be_agent')]);
        else
            return false;
    }

    public function isReceiverPersonal($receiver)
    {
        if ($receiver->user_type_id != UserTypeId::PERSONAL)
            return $this->invalidResponse([trans('messages.receiver_account_must_be_personal')]);
        else
            return false;
    }

    public function senderIsNotReceiver($sender, $receiver)
    {
        if ($sender->id == $receiver->id)
            return $this->invalidResponse([trans('messages.please_select_another_user')]);
        else
            return false;
    }

    public function isReceiverAgent($receiver)
    {
        if ($receiver->user_type_id != UserTypeId::AGENT)
            return $this->invalidResponse([trans('messages.receiver_account_must_be_agent')]);
        else
            return false;
    }

    public function senderParentIsMainAgent(User $sender)
    {
        if (!$this->isParentMainAgent($sender))
            return $this->invalidResponse([trans('messages.sender_parent_must_be_main_agent')]);
        else
            return false;
    }

    public function receiverIsSenderParent(User $receiver)
    {
        if ($receiver->id != $this->getAgentParent(auth()->user())->sales_rep_id)
            return $this->invalidResponse([trans('messages.receiver_must_be_agent_parent')]);
        else
            return false;
    }

    public function isSenderAccountActive($sender)
    {
        if ($sender->status != 1) {
            return $this->invalidResponse([trans('messages.sender_account_must_be_active')]);
        }

        if ($sender->user_status_id == UserStatusId::PERMANENTLY_CLOSED) {
            return $this->invalidResponse([trans('messages.sender_account_permanently_closed')]);
        }

        // if ($sender->user_status_id != UserStatusId::APPROVED) {
        //     return $this->invalidResponse([ trans('messages.sender_account_must_be_active') ]);
        // }

        // $account = UserAccount::where('user_id', $sender->id)
        //     ->whereHas('accountBalances', function ($query) {
        //        $query->where('currency_id', config('basic_settings.currency_id'));
        //     })
        //     ->first();

        // if ($account->status != 1) {
        //     return $this->invalidResponse([ trans('messages.sender_account_must_be_active') ]);
        // }

        return false;
    }

    public function isReceiverAccountActive($receiver)
    {
        if ($receiver->status != 1) {
            return $this->invalidResponse([trans('messages.receiver_account_must_be_active')]);
        }

        if ($receiver->user_status_id == UserStatusId::PERMANENTLY_CLOSED) {
            return $this->invalidResponse([trans('messages.receiver_account_permanently_closed')]);
        }

        // if ($receiver->user_status_id != UserStatusId::APPROVED) {
        //     return $this->invalidResponse([ trans('messages.receiver_account_must_be_active') ]);
        // }

        // $account = UserAccount::where('user_id', $receiver->id)
        //     ->whereHas('accountBalances', function ($query) {
        //         $query->where('currency_id', config('basic_settings.currency_id'));
        //     })
        //     ->first();

        // if ($account->status != 1) {
        //     return $this->invalidResponse([ trans('messages.receiver_account_must_be_active') ]);
        // }

        return false;
    }

    public function isSenderKycVerified($sender)
    {
        if ($sender->is_kyc_verified != 1)
            return $this->invalidResponse([trans('messages.sender_kyc_is_not_verified')]);
        else
            return false;
    }

    public function isReceiverKycVerified($receiver)
    {
        if ($receiver->is_kyc_verified != 1)
            return $this->invalidResponse([trans('messages.receiver_kyc_is_not_verified')]);
        else
            return false;
    }

    public function senderAndReceiverHaveSameParent($sender, $receiver)
    {
        if (!$this->areSenderAndReceiverHasSameParent($sender, $receiver))
            return $this->invalidResponse([trans('messages.sender_and_receiver_must_have_same_parent')]);
        else
            return false;
    }

    public function senderAndReceiverFromSameCity($sender, $receiver)
    {
        if (!$this->isSenderReceiverFromSameCity($sender, $receiver))
            return $this->invalidResponse([trans('messages.sender_and_receiver_not_from_same_city')]);
        else
            return false;
    }

    public function hasSufficientBalanceInSavingsAccount($sender, $receiver, string $currency, int $amount, int $transactionTypeId)
    {
        # Does sender have any savings account
        $senderSavingsAccount = $sender->accounts->where('user_account_type_id', '=', 1)->first();

        if (!$senderSavingsAccount)
            return $this->invalidResponse([trans('messages.sender_savings_account_not_found')]);

        # Does receiver have any savings account
        $receiverSavingsAccount = $receiver->accounts->where('user_account_type_id', '=', 1)->first();

        if (!$receiverSavingsAccount)
            return $this->invalidResponse([trans('messages.receiver_savings_account_not_found')]);

        # Does sender have sufficient balance in required currency
        $currencyId = 0;
        if ($currency == 'IQD')
            $currencyId = 103;

        # Does sender have balance in specified currency
        $senderBalanceInSpecifiedCurrency = $senderSavingsAccount->accountBalances->where('currency_id', '=', $currencyId)->first();

        if (!$senderBalanceInSpecifiedCurrency)
            return $this->invalidResponse([trans('messages.sender_has_no_balance_in_specified_currency')]);

        # Does receiver have balance in specified currency
        $receiverBalanceInSpecifiedCurrency = $receiverSavingsAccount->accountBalances->where('currency_id', '=', $currencyId)->first();

        if (!$receiverBalanceInSpecifiedCurrency)
            return $this->invalidResponse([trans('messages.receiver_has_no_balance_in_specified_currency')]);

        if ($senderBalanceInSpecifiedCurrency->balance < $amount)
            return $this->invalidResponse([trans('messages.not_sufficient_amount_to_transfer')]);


        if ($this->checkDuplicateTrx($senderSavingsAccount->id, $receiverSavingsAccount->id, $transactionTypeId, $amount)) {
            return $this->invalidResponse([trans('messages.duplicate_transaction_exception', ['duration' => config('basic_settings.duplicate_trx_time_diff')])]);
        }


        return false;
    }


    private function checkDuplicateTrx($senderAccountId, $receiverAccountId, $trxTypeId, $amount)
    {
        $lastTransactionOfThisSenderToSameReceiver = $this->checkDuplicateTransaction($senderAccountId, $receiverAccountId, $trxTypeId, $amount);

        if ($lastTransactionOfThisSenderToSameReceiver) {
            Mail::to('mahfuzul.alam@fast-pay.cash')
                ->send(
                    new TriedToSendDuplicateTransferWithinTimeLimit($lastTransactionOfThisSenderToSameReceiver)
                );
        }

        return $lastTransactionOfThisSenderToSameReceiver;
    }
}
