<?php


namespace App\Domain\Transaction\Models;

use App\Domain\Accounting\Models\AccountBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GL_Transaction extends Model
{
    use SoftDeletes;
    protected $table = "gl_transactions";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function accountBalance()
    {
        return $this->belongsTo(AccountBalance::class, 'receiver_account_balance_id');
    }
}
