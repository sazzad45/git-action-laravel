<?php

namespace App\Http\Controllers\API\V1\Notification;

use App\Domain\Independent\Models\Notification;
use App\Http\Controllers\APIBaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationReadController extends APIBaseController
{
    public function update(Request $request)
    {
        try {
            $user = auth()->user();

            $notification = Notification::where('id', $request->id)
                ->where('notifiable_type', "App\\Domain\\UserRelation\\Models\\User")
                ->where('notifiable_id', $user->id);

            if($notification->count() == 0)
            {
                $this->invalidResponse([
                    'Notification not found'
                ]);
            }

            $notification = $notification->first();
            $notification->read_at = date('Y-m-d H:i:s');
            $notification->update();

            return $this->successResponse(['Notification Read'], null);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
