<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionalOffer extends Model
{
    use SoftDeletes;
    protected $table = "promotional_offers";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function getBannerAttribute($value)
    {
        return env('STATIC_URL')."/".$value;
    }
}
