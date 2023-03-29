<?php

namespace App\Domain\Wallet\Models\Capacity;

use App\Domain\Level\Models\Level;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Independent\Models\Currency;
use App\Domain\UserRelation\Models\UserType;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountCapacity extends Model
{
    use SoftDeletes;

    protected $table = "account_capacities";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeActive($query)
    {
        return $query->whereStatus(1);
    }
}
