<?php

namespace App\Domain\Wallet\Models\Limit;

use App\Domain\Accounting\Models\UserAccountType;
use App\Domain\Independent\Models\Currency;
use App\Domain\Level\Models\Level;
use App\Domain\Transaction\Models\TransactionType;
use App\Domain\UserRelation\Models\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Limit extends Model
{
    use SoftDeletes;

    protected $table = "limits";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function accountType()
    {
        return $this->belongsTo(UserAccountType::class, 'user_account_type_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function limitType()
    {
        return $this->belongsTo(LimitType::class);
    }
}
