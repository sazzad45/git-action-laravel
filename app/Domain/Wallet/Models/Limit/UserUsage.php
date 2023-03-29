<?php

namespace App\Domain\Wallet\Models\Limit;

use App\Domain\Transaction\Models\TransactionType;
use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserUsage extends Model
{
    protected $table = "user_usages";

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'action_date'
    ];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }
}
