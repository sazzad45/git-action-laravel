<?php

namespace App\Domain\Merchant\Models;

use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProfile extends Model
{
    use SoftDeletes;
    protected $table = "business_profiles";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(User::class, 'business_id');
    }
}
