<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashbackTransaction extends Model
{
    use SoftDeletes;
    protected $table = "cashback_transactions";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function originalTransaction()
    {
        return $this->belongsTo('transactions', 'original_trx_unq_id', 'tx_unique_id');
    }

    public function newTransaction()
    {
        return $this->belongsTo('transactions', 'new_trx_unq_id', 'tx_unique_id');
    }

    public function cashbackReward()
    {
        return $this->belongsTo('cash_back_rewards', 'cash_back_reward_id', 'id');
    }
}
