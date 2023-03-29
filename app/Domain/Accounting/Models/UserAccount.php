<?php

namespace App\Domain\Accounting\Models;

use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccountType;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccount extends Model
{
    use SoftDeletes;
    protected $table = "user_accounts";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userAccountType()
    {
        return $this->belongsTo(UserAccountType::class);
    }

    public function accountBalances()
    {
        return $this->hasMany(AccountBalance::class);
    }
}
