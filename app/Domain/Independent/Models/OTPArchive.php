<?php

namespace App\Domain\Independent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OTPArchive extends Model
{
    use SoftDeletes;
    protected $table = "otp_archives";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
}
