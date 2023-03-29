<?php

namespace App\Http\Controllers\API\V1\Wallet\Recipient;

use App\Constant\TransactionTypeText;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\FastPay\Constant\APIEndPoints;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Models\TransactionType;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RecentRecipientController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $recipients = $this->fetchRecentRecipients($request);

            return $this->respondInJSON(200, [], [
                'recipients' => $recipients
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function fetchRecentRecipients(Request $request)
    {
        $account = UserAccount::select('id')->where('user_id', $request->user()->id)->first();

        $transactionTypeId = TransactionType::where('name', TransactionTypeText::P2P_TRANSFER)->first()->id;
        $transactions = DB::select(DB::raw("select distinct receiver_id, count(receiver_id), max(id) as id, sender_id from `transactions` where `sender_id` = {$account->id} and `transaction_type_id` = {$transactionTypeId} and `transactions`.`deleted_at` is null group by `receiver_id` ORDER BY id limit 8"));

        $transaction = Transaction::with('sender.user', 'receiver.user')
            ->whereIn('id', is_object($transactions) ? $transactions->pluck('id') : array_column($transactions, 'id'))
            ->get()
            ->map(function ($transaction) {
                return [
                    "name" => $transaction->receiver->user->name,
                    "mobile_number" => $transaction->receiver->user->mobile_no,
                    "avatar" => $transaction->receiver->user->avatar
                ];
            });
        return $transaction;
    }
}
