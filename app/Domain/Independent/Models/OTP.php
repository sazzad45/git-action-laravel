<?php

namespace App\Domain\Independent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class OTP extends Model
{
    use SoftDeletes, LogsActivity;
    protected $table = "otps";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['identity', 'otp', 'purpose', 'client', 'ip_address', 'status'];
    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "OTP {$eventName}";
    }
}
