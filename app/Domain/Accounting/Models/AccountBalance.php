<?php


namespace App\Domain\Accounting\Models;

use App\Domain\Independent\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountBalance extends Model
{
    use SoftDeletes;
    protected $table = "account_balances";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
