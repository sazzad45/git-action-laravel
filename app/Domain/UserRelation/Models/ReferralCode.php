<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralCode extends Model
{
    use SoftDeletes;
    protected $table = "referral_codes";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function owner(){
        return $this->belongsTo(User::class);
    }

    public function referredUser(){
        return $this->belongsTo(User::class);
    }
}
