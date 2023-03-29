<?php

namespace App\Domain\Distribution\Models;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PotentialAgent extends Model
{
    use SoftDeletes;

    protected $table = "potential_agents";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
