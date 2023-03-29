<?php

namespace App\Domain\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccountType extends Model
{
    use SoftDeletes;

    protected $table = "user_account_types";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function userAccounts()
    {
        return $this->hasMany(UserAccount::class);
    }
}
