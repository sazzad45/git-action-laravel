<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentProfile extends Model
{
    use SoftDeletes;
    protected $table = "agent_profiles";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    protected $fillable = [
        'business_name',
        'mobile_no',
        'business_time',
        'business_days',
        'system_type',
        'address',
        'logo',
        'trade_license',
        'latitude',
        'longitude'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
