<?php

namespace App\Domain\Wallet\Models;

use App\Domain\Independent\Models\Currency;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyRequest extends Model
{
    use SoftDeletes;

    public $incrementing = false;

    protected $table = "money_requests";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
    protected $fillable = ['amount', 'message', 'transaction_id'];

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function requestee()
    {
        return $this->belongsTo(User::class, 'requestee_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
