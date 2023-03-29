<?php

namespace App\Domain\Wallet\Models\Limit;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMonthlyLimit extends Model
{
    use SoftDeletes;

    protected $table = "user_monthly_limits";

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'limit_id',
        'action_month',
        'action_year',
        'number_of_tx',
        'tx_amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function monthlyLimit()
    {
        return $this->belongsTo(Limit::class, 'monthly_limit_id');
    }
}
