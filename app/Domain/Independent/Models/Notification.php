<?php

namespace App\Domain\Independent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    protected $table = "notifications";
    protected $dates = ['created_at', 'updated_at'];
    protected $guarded = ['id'];
}
