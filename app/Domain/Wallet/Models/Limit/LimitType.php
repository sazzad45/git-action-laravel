<?php

namespace App\Domain\Wallet\Models\Limit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimitType extends Model
{
    use SoftDeletes;
    protected $table = "limit_types";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
}
