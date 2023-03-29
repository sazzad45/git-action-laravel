<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PasswordReset extends Model
{
    use SoftDeletes;

    protected $table = "password_resets";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function resetRequestBy()
    {
        return $this->belongsTo(User::class, 'reset_req_by');
    }
}
