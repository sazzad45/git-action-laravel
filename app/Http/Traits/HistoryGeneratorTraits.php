<?php


namespace App\Http\Traits;

trait HistoryGeneratorTraits
{
    public function getPartiesName($transaction, $account)
    {
        $parties = [
            'source' => null,
            'destination' => null
        ];

        if ($transaction->sender_id == $account->id) {
            $parties['destination'] = [
                'name' => $transaction->receiver->user->original_name ?? "",
                'mobile_no' => $transaction->receiver->user->mobile_no ?? "",
            ];
        } else {
            $parties['source'] = [
                'name' => $transaction->sender->user->original_name ?? "",
                'mobile_no' => $transaction->sender->user->mobile_no ?? "",
            ];
        }

        return $parties;
    }
}