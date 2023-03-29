<?php

namespace App\Domain\Wallet\Models\Limit;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserUsageSummary extends Model
{
    protected $table = "user_usage_summaries";

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
