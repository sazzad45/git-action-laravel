<?php

namespace App\Domain\Finance\Models;

use App\Domain\Independent\Models\Currency;
use App\Domain\LocationManagement\Models\Country;
use App\Domain\Transaction\Models\TransactionType;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use SoftDeletes;
    protected $table = "commissions";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];


    public function business()
    {
        return $this->belongsTo(User::class, 'business_id', 'id');
    }

    public function senderType()
    {
        return $this->belongsTo(UserType::class, 'sender_type_id');
    }

    public function receiverType()
    {
        return $this->belongsTo(UserType::class, 'receiver_type_id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
