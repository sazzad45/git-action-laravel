<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\B2BTransfer;

use App\Constant\TransactionType;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Transaction\Models\Transaction;
use App\Http\Controllers\APIBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecentRecipientController extends APIBaseController
{
    public function index()
    {
        try {
            return $this->respondInJSON(200, [], [
                'recipients' => $this->fetchRecentRecipients()
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function fetchRecentRecipients()
    {
        $account = UserAccount::select('id')->where('user_id', auth()->user()->id)->first();
        $transactions = DB::select(DB::raw("SELECT DISTINCT receiver_id, COUNT(receiver_id), MAX(id) AS id, sender_id FROM `transactions` WHERE `sender_id` = {$account->id} AND `transaction_type_id` = " . TransactionType::B2B_TRANSFER . " AND `transactions`.`deleted_at` IS NULL GROUP BY `receiver_id` ORDER BY id DESC LIMIT 8"));

        return Transaction::with('sender.user', 'receiver.user')
            ->whereIn('id', is_object($transactions)?$transactions->pluck('id'):array_column($transactions,'id'))
            ->latest('id')
            ->get()
            ->map(function($transaction) {
                return [
                    "name" => $transaction->receiver->user->name,
                    "mobile_number" => $transaction->receiver->user->mobile_no,
                    "avatar" => $transaction->receiver->user->avatar
                ];
            });
    }
}
