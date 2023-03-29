<?php

namespace App\Domain\Distribution\Models;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentLocation extends Model
{
    use SoftDeletes;

    protected $table = "agent_locations";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sr_user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_user_id');
    }
}
