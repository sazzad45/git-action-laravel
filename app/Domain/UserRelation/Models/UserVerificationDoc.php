<?php

namespace App\Domain\UserRelation\Models;

use App\Domain\LocationManagement\Models\City;
use App\Domain\LocationManagement\Models\Country;
use App\Domain\LocationManagement\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVerificationDoc extends Model
{
    use SoftDeletes;
    protected $table = "user_verification_docs";
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'issue_date', 'expiry_date', 'date_of_birth'];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuingCountry()
    {
        return $this->belongsTo(Country::class, 'issuing_country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function verificationDocType()
    {
        return $this->belongsTo(UserVerificationDocType::class, 'verification_docs_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
