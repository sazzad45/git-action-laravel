<?php

namespace App\Domain\Transaction\Models;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statement extends Model
{
    use SoftDeletes;
    protected $table = "statements";
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getreportDateAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}
