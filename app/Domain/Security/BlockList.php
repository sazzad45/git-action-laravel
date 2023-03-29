<?php

namespace App\Domain\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockList extends Model
{
    use SoftDeletes;
    protected $table = "block_lists";
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'blocked_at','unblock_at'];
}
