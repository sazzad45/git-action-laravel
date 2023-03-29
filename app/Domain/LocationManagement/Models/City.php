<?php

namespace App\Domain\LocationManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;
    protected $table = "cities";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function zip_codes()
    {
        return $this->hasMany(ZipCode::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function scopeActive($query)
    {
        return $query->whereStatus(1);
    }

    public function scopeFilterByState($query, $request)
    {
        if ($request->filled('state_id'))
        {
            return $query->whereStateId($request->state_id);
        }
    }
}
