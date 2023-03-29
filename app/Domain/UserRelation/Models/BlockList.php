<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockList extends Model
{
    use SoftDeletes;
    protected $table = "block_lists";
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'unblock_at'];
    protected $guarded = ['id'];
}
