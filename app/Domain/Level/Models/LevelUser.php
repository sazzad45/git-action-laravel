<?php

namespace App\Domain\Level\Models;

use Illuminate\Database\Eloquent\Model;

class LevelUser extends Model
{
    protected $table = "level_users";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
}
