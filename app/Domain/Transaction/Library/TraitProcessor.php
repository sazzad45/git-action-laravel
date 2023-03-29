<?php


namespace App\Domain\Transaction\Library;


use App\Domain\Transaction\Models\GL_Transaction;
use App\Domain\Transaction\Models\Statement;
use App\Domain\Transaction\Models\Transaction;

trait TraitProcessor
{
    private function saveOnStatement(Transaction $transaction, DebitableAccount $item, $currentBalance)
    {
        $data = [
            'transaction_id' => $transaction->id,
            'transaction_type_id' => $transaction->transaction_type_id,
            'user_id' => $item->user_id,
            'description' => $item->description,
            'debit' => $item->amount,
            'credit' => 0,
            'current_balance' => $currentBalance,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->created_at
        ];
        Statement::create($data);
    }

    private function saveOnStatementForCredit(Transaction $transaction, CreditableAccount $item, $currentBalance)
    {
        $data = [
            'transaction_id' => $transaction->id,
            'transaction_type_id' => $transaction->transaction_type_id,
            'user_id' => $item->user_id,
            'description' => $item->description,
            'debit' => 0,
            'credit' => $item->amount,
            'current_balance' => $currentBalance,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->created_at
        ];
        Statement::create($data);
    }
}
