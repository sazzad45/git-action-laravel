<?php

namespace App\Http\Controllers\API\V1\Wallet\History;

use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Transaction\Models\Transaction;
use App\Http\Controllers\APIBaseController;
use App\Http\Traits\HistoryGeneratorTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionHistoryController extends APIBaseController
{
    use HistoryGeneratorTraits;
    public function index(Request $request)
    {
        try {
            $has_next_page = false;
            $transactions = $this->fetchTransactionHistory($request, $has_next_page);

            return $this->respondInJSON(200, [], [
                'transactions' => $transactions,
                'has_next_page' => $has_next_page
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function fetchTransactionHistory(Request $request, &$has_next_page)
    {
        $transactionList = [];
        $accounts = UserAccount::select('id')->where('user_id', $request->user()->id)->get();

        foreach($accounts as $account) {
            $transactions = Transaction::with('sender.user', 'receiver.user', 'transactionType')
                ->where(function($query) use ($account) {
                    $query->where('sender_id', $account->id)
                        ->orWhere('receiver_id', $account->id);
                })
                ->latest('id')
                ->paginate(10);

            $has_next_page = $transactions->nextPageUrl() ? true : false;

            foreach ($transactions as $transaction) {

                if (! $transaction->sender->user) continue;
                if (! $transaction->receiver->user) continue;

                $parties = $this->getPartiesName($transaction, $account);

                $transactionList[] = [
                    'color' => ($transaction->sender_id == $account->id) ? '#FC2861' : '#03EBA3',
                    'icon' => ($transaction->sender_id == $account->id) ? secure_asset('image/icons/send.png') : secure_asset('image/icons/receive.png'),
                    'title' => ($transaction->sender_id == $account->id) ? 'Money Sent' : 'You Received Money',
                    'transaction_id' => $transaction->tx_unique_id,
                    'amount' => (int)$transaction->amount,
                    'currency' => config('fastpay.currency_text'),
                    'transaction_type' => $transaction->transactionType->name,
                    'created_at' => $transaction->created_at->format('j F Y H:i A'),
                    'source' => $parties['source'],
                    'destination' => $parties['destination'],
                    'type_of_tx' => ($transaction->sender_id == $account->id) ? 'Debit' : 'Credit'
                ];
            }
        }

        return $transactionList;
    }

}
