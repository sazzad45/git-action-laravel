<?php

namespace App\Domain\Transaction\Models;

use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Finance\Models\Commission;
use App\Domain\Independent\Models\Currency;
use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $table = "transactions";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function sender()
    {
        return $this->belongsTo(UserAccount::class, 'sender_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(UserAccount::class, 'receiver_id', 'id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function transactionStatus()
    {
        return $this->belongsTo(TransactionStatus::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }


    public function statements()
    {
        return $this->hasMany(Statement::class);
    }
}


