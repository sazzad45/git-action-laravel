<?php

namespace App\Domain\Transaction\Library;

use App\Constant\TransactionTypeCode;
use App\Domain\Transaction\Library\CashIn\Processor as CashInPaymentProcessor;
// use App\Domain\Transaction\Library\BundlePurchase\Processor as BundlePurchaseProcessor;
use App\Domain\Transaction\Library\B2BTransfer\Processor as B2BTransferProcessor;
use App\Domain\Transaction\Library\CashOut\Processor as CashOutPaymentProcessor;

class TransactionGenerator
{
    private $param;

    private $transactionType;

    public function __construct($param, $transactionType)
    {
        $this->param = $param;
        $this->transactionType = $transactionType;
    }

    public function process()
    {
        if($this->transactionType == TransactionTypeCode::CASH_IN)
        {
            return (new CashInPaymentProcessor($this->param))->process();
        }

        // if($this->transactionType == TransactionTypeCode::BUNDLE_PURCHASE)
        // {
        //     return (new BundlePurchaseProcessor($this->param))->process();
        // }

        if($this->transactionType == TransactionTypeCode::B2B_TRANSFER)
        {
            return (new B2BTransferProcessor($this->param))->process();
        }

        if($this->transactionType == TransactionTypeCode::CASH_OUT)
        {
            return (new CashOutPaymentProcessor($this->param))->process();
        }
    }
}
