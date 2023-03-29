<?php

namespace App\Domain\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomingRequest extends Model
{
    use SoftDeletes;
    protected $table = "incoming_requests";
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
