<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ActivityLogTrait
{
    protected  function logActivity($logMessage, $performedOn, $causedBy = null, $properties = [])
    {
        try {
            activity()
                ->causedBy($causedBy)
                ->performedOn($performedOn)
                ->withProperties($properties)
                ->log($logMessage);
        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }
}
