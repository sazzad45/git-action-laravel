<?php


namespace App\Traits;

use App\Domain\Transaction\Models\Transaction;
use Carbon\Carbon;

trait DuplicateTransactionCheckerTrait
{
    protected function checkDuplicateTransaction($senderAccountId, $receiverAccountId, $txType, $amountWithCharge)
    {
        $lastTransactionOfThisSenderToSameReceiver = Transaction::whereSenderId($senderAccountId)
            ->whereReceiverId($receiverAccountId)
            ->where('transaction_type_id', $txType)
            ->whereRaw(" ( amount + sender_commission ) = ?", $amountWithCharge)
            ->latest()
            ->first();

        if ($lastTransactionOfThisSenderToSameReceiver) {
            $now = Carbon::now();
            $diffInSeconds = $now->diffInSeconds($lastTransactionOfThisSenderToSameReceiver->updated_at);

            if ($diffInSeconds < config('basic_settings.duplicate_trx_time_diff'))
                return $lastTransactionOfThisSenderToSameReceiver;
        }

        return false;
    }
}
