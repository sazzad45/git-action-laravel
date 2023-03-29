<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Occupation extends Model
{
    use SoftDeletes;
    protected $table = "occupations";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->whereStatus(1);
    }

    public function getIconAttribute($value)
    {
        return Storage::disk('custom')->url($value);
    }
}
