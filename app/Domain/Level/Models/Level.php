<?php

namespace App\Domain\Level\Models;

use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use SoftDeletes;
    protected $table = "levels";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function userTypes(){
        return $this->belongsToMany(UserType::class, 'level_user_type');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'level_users', 'level_id', 'id');
    }
}
