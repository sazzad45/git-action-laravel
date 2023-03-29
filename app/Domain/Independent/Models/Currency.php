<?php

namespace App\Domain\Independent\Models;

use App\Domain\LocationManagement\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;
    protected $table = "currencies";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

//    public static function boot ()
//    {
//        parent::boot();
//
//        self::creating(function ($model) {
//
//            $model->created_by = auth()->user()->id;
//
//        });
//    }
}
