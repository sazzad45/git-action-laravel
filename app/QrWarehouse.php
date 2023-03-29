<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QrWarehouse extends Model
{
    public $incrementing = false;

    protected $guarded = ['created_at', 'updated_at'];
}
