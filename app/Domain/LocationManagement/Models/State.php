<?php

namespace App\Domain\LocationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;
    protected $table = "states";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function scopeActive($query)
    {
        return $query->whereStatus(1);
    }

    public function scopeFilterByCountry($query, $request)
    {
        if ($request->filled('country_id'))
        {
            return $query->whereCountryId($request->country_id);
        }
    }
}
