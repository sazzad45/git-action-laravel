<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserVerificationDocType extends Model
{
    use SoftDeletes;
    protected $table = "user_verification_doc_types";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];
    protected $fillable = ['name', 'status'];

    public function userVerificationDocs()
    {
        return $this->hasMany(UserVerificationDoc::class);
    }

    public function getIconAttribute($value)
    {
        return Storage::disk('custom')->url($value);
    }

    public function scopeActive($query)
    {
        return $query->whereStatus(1);
    }
}
