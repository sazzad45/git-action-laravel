<?php

namespace App\Domain\UserRelation\Models;

use App\Domain\LocationManagement\Models\City;
use App\Domain\LocationManagement\Models\Country;
use App\Domain\LocationManagement\Models\State;
use App\Domain\LocationManagement\Models\ZipCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use SoftDeletes;
    protected $table = "user_profiles";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function zipCode()
    {
        return $this->belongsTo(ZipCode::class);
    }
}
