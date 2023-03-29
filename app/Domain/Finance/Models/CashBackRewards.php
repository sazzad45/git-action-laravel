<?php

namespace App\Domain\Finance\Models;

use App\Domain\Accounting\Models\UserAccountType;
use App\Domain\Independent\Models\Currency;
use App\Domain\Level\Models\Level;
use App\Domain\Transaction\Models\TransactionType;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBackRewards extends Model
{
    use SoftDeletes;
    protected $table = "cash_back_rewards";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function senderUserType()
    {
        return $this->belongsTo(UserType::class, 'sender_user_type_id');
    }

    public function receiverUserType()
    {
        return $this->belongsTo(UserType::class, 'receiver_user_type_id');
    }

    public function senderLevel()
    {
        return $this->belongsTo(Level::class, 'sender_level_id');
    }

    public function receiverLevel()
    {
        return $this->belongsTo(Level::class, 'receiver_level_id');
    }

    public function senderAccountType()
    {
        return $this->belongsTo(UserAccountType::class, 'sender_account_type_id');
    }

    public function receiverAccountType()
    {
        return $this->belongsTo(UserAccountType::class, 'receiver_account_type_id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
