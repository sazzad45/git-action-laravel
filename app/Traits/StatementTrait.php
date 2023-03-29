<?php


namespace App\Traits;

use App\Domain\Transaction\Models\Statement;
use App\Domain\Transaction\Models\Transaction;

trait StatementTrait
{
    private function saveOnStatement(Transaction $transaction, $userId, $description, $creditAmount, $debitAmount, $currentBalance)
    {
        $data = [
            'transaction_id' => $transaction->id,
            'transaction_type_id' => $transaction->transaction_type_id,
            'user_id' => $userId,
            'description' => $description,
            'debit' => $debitAmount,
            'credit' => $creditAmount,
            'current_balance' => $currentBalance,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->created_at
        ];
        Statement::create($data);
    }
}
