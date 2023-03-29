<?php

namespace App\Domain\Independent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TLD extends Model
{
    use SoftDeletes;
    protected $table = "tlds";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
}
