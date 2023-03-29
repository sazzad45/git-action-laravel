<?php


namespace App\Http\Controllers\API\V1\Notification;


use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class NotificationController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            $per_page = 50;
            $total_count = 0;
            $has_next_page = false;
            $unread_count = 0;
            $notifications = []; //$this->fetchNotifications($request, $per_page, $total_count, $has_next_page, $unread_count);

            return $this->respondInJSONWithAdditional(200, [], [
                'notifications' => $notifications,
                'has_next_page' => $has_next_page,
                'unread_count' => $unread_count
            ], $per_page, $total_count);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function fetchNotifications(Request $request, &$per_page, &$total_count, &$has_next_page, &$unread_count)
    {
        $notifications = $request->user()->notifications();
        $total_count = $notifications->count();
        $unread_count = $this->countUnreadNotification($notifications);
        $notifications = $notifications->paginate($per_page);

        $has_next_page = $notifications->nextPageUrl() ? true : false;

        return $notifications->map(function($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification['data']['title'],
                'sub_title' => $notification['data']['sub_title'],
                'title_color' => '#03EBA3',
                'icon' => secure_asset('image/icons/money-requests/accepted.png'),
                'description' => $notification['data']['description'],
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'jump_to' => null,
                'read_at' => $notification->read_at != null ? $notification->read_at->format('Y-m-d H:i:s') : null
            ];
        });
    }

    private function countUnreadNotification($notifications): int
    {
        $count = 0;
        $notifications->each(function ($notification) use (&$count) {
            if($notification->read_at == null) $count++;
        });
        return $count;
    }
}
