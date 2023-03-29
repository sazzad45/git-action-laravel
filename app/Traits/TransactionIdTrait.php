<?php


namespace App\Traits;


use App\Domain\Transaction\Models\Transaction;
use Carbon\Carbon;

trait TransactionIdTrait
{
    public function generateCode($dateTime, $mobileNo)
    {
        $minuteAbove = $secondAbove = "0";
        $formattedDateString = substr(Carbon::parse($dateTime)->format('YmdHis'), 2, strlen(date('YmdHis')));

        $dateMapArrayWithin = config('transaction.code_mapping.value_within');
        $dateMapArrayAbove = config('transaction.code_mapping.value_above');
        $randomMapArray = config('transaction.code_mapping.random_value');

        $newDateArray = [];
        for($i=0; $i<12; $i +=2)
        {
            $newDateArray[] = substr($formattedDateString, $i, 2);
        }

        $modifiedDateArray = [];
        for($i=0; $i<6; $i++)
        {
            if($i > 3){
                if($newDateArray[$i] > 33){
                    if(isset($dateMapArrayAbove[$newDateArray[$i]])){
                        $modifiedDateArray[] = $dateMapArrayAbove[$newDateArray[$i]];
                    }
                    $i == 4 ? $minuteAbove = "1" : $secondAbove = "1";
                    continue;
                }
                if(isset($dateMapArrayWithin[$newDateArray[$i]])){
                    $modifiedDateArray[] = $dateMapArrayWithin[$newDateArray[$i]];
                }
                continue;
            }
            if(isset($dateMapArrayWithin[$newDateArray[$i]])){
                $modifiedDateArray[] = $dateMapArrayWithin[$newDateArray[$i]];
            }
        }

        $firstChar = $randomMapArray[$minuteAbove.$secondAbove];
        $modifiedDateString = implode('', $modifiedDateArray);
        $transactionCode = $firstChar.$modifiedDateString.substr($mobileNo, -3);

        return $transactionCode;
    }

    protected function getUniqueTransactionId($datetime, $senderMobileNumber, $receiverMobileNumber)
    {
        $uniqueCode = $this->generateCode($datetime, $senderMobileNumber);

        $instantTransaction = Transaction::where('created_at', $datetime)
            ->where('tx_unique_id', $uniqueCode)
            ->first();

        if(! $instantTransaction)
            return $uniqueCode;

        return $this->generateCode($datetime, $receiverMobileNumber);
    }
}
