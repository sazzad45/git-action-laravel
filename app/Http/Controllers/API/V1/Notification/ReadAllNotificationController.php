<?php

namespace App\Http\Controllers\API\V1\Notification;

use App\Domain\Independent\Models\Notification;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReadAllNotificationController extends APIBaseController
{
    public function updateAll(Request $request)
    {
        try {
            $user = auth()->user();

            Notification::where('notifiable_type', "App\\Domain\\UserRelation\\Models\\User")
                ->whereNull('read_at')
                ->where('notifiable_id', $user->id)
                ->update(['read_at' => date('Y-m-d H:i:s')]);

            return $this->successResponse(['Notification Read'], null);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
