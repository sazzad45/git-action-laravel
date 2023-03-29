<?php

namespace App\Domain\Merchant\Models;

use App\Domain\Business\Models\BusinessCategory;
use App\Domain\Business\Models\BusinessType;
use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreConfiguration extends Model
{
    use SoftDeletes;
    protected $table = "store_configurations";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function getName()
    {
        return $this->hasOne(User::class, 'id','merchant_id');
    }
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class);
    }
}
