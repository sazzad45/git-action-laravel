<?php

namespace App\Domain\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FailedLoginAttempt extends Model
{
    use SoftDeletes;
    protected $table = "failed_login_attempts";
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
