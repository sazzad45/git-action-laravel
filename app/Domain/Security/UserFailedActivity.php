<?php

namespace App\Domain\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFailedActivity extends Model
{
    use SoftDeletes;
    protected $table = "user_failed_activities";
    protected $guarded = ['id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
//
