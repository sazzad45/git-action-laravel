<?php

namespace App\Domain\Finance\Models;

use App\Domain\Accounting\Models\AccountBalance;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Level\Models\Level;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LevelAccount extends Model
{
    use SoftDeletes;
    protected $table = "level_account";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];


    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_account_id', 'id');
    }

    public function accountBalance()
    {
        return $this->belongsTo(AccountBalance::class, 'account_balance_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->whereStatus(1);
    }
}
