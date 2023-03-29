<?php

namespace App\Domain\Wallet\Library\Validator;

use App\Domain\Accounting\Models\AccountBalance;
use App\Exceptions\Transaction\TransactionValidatorException;
use App\Traits\DuplicateTransactionCheckerTrait;

class TransactionValidator
{
    use DuplicateTransactionCheckerTrait;

    private $senderBalanceAccount;
    private $senderAccountId;
    private $receiverAccountId;
    private $transactionTypeId;
    private $amountWithCharge;
    private $duplicateTrxCheck;

    public function __construct(AccountBalance $senderBalanceAccount, $senderAccountId, $receiverAccountId,  int $transactionTypeId, $amountWithCharge, bool $duplicateTrxCheck = true)
    {
        $this->senderBalanceAccount = $senderBalanceAccount;
        $this->senderAccountId = $senderAccountId;
        $this->receiverAccountId = $receiverAccountId;
        $this->transactionTypeId = $transactionTypeId;
        $this->amountWithCharge = $amountWithCharge;
        $this->duplicateTrxCheck = $duplicateTrxCheck;
    }

    public function validate()
    {
        $this->checkSenderHasSufficientBalanceToTransfer();

        if ($this->duplicateTrxCheck) {
            $this->checkIfTheTransactionDuplicate();
        }
    }

    private function checkSenderHasSufficientBalanceToTransfer()
    {
        if ($this->senderBalanceAccount->balance < $this->amountWithCharge) {
            throw new TransactionValidatorException(trans('messages.not_sufficient_amount_to_transfer'));
        }
    }

    private function checkIfTheTransactionDuplicate()
    {
        if ($this->checkDuplicateTransaction($this->senderAccountId, $this->receiverAccountId, $this->transactionTypeId, $this->amountWithCharge)) {
            throw new TransactionValidatorException(trans('messages.duplicate_transaction_exception', ['duration' => config('basic_settings.duplicate_trx_time_diff')]));
        }
    }
}