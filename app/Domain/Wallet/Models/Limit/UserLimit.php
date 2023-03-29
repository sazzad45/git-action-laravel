<?php

namespace App\Domain\Wallet\Models\Limit;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserLimit extends Model
{
    protected $table = "user_limits";
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'action_date'
    ];
    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'limit_id',
        'action_date',
        'number_of_tx',
        'tx_amount',
        'number_of_tx_of_month',
        'tx_amount_of_month'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyLimit()
    {
        return $this->belongsTo(Limit::class, 'daily_limit_id');
    }
}
