<?php


namespace App\Domain\Transaction\Utility;


class TransactionType
{
    const SEND_MONEY = 0;

    const BUNDLE_PURCHASE = 1;

    const DEPOSIT_MONEY = 2;

    const WITHDRAW_MONEY = 3;

    const PAYMENT = 4;

    const CASH_IN = 5;
}
