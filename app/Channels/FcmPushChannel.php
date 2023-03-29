<?php


namespace App\Channels;

use App\Domain\UserRelation\Models\UserDevice;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toFcmPush($notifiable);

        $userDevice = UserDevice::where('user_id', $notifiable->id)->orderBy('id', 'DESC')->first();

        $fields = array();
        if ($userDevice == "") return;

        if (strtolower($userDevice->push_platform) == "firebase") {

            if (strtolower($userDevice->push_os) == "ios") {
                $fields = array(
                    "to" => $userDevice->fcm_key,
                    "priority" => "high",
                    "data" => [
                        "title" => $message['title'],
                        "message" => $message['description'],
                        "action_url" => isset($message['action_url']) ? $message['action_url'] : ''
                    ],
                    "notification" => [
                        "title" => $message['title'],
                        "body" => $message['description']
                    ]
                );
            } else if (strtolower($userDevice->push_os) == "android") {
                $fields = array(
                    "to" => $userDevice->fcm_key,
                    "priority" => "high",
                    "data" => [
                        "title" => $message['title'],
                        "message" => $message['description'],
                        "action_url" => isset($message['action_url']) ? $message['action_url'] : ''
                    ]
                );
            }

            $fields = json_encode($fields);

            $this->sendViaFirebase($fields);
        } else {
            $fields = [
                'token' => $userDevice->fcm_key,
                'title' => $message['title'],
                'message' => $message['description'],
                'action_url' => $message['action_url'] ?? '',
                'type' => isset($message['action_url']) ? 1 : 3
            ];

            $this->sendViaHuaweiPush($fields, $notifiable);
        }
    }

    private function sendViaFirebase($fields)
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $headers = array(
                'Authorization: key=AAAAf61K_UY:APA91bFvpGO7BFMl2TPBlXU28z9PajnOF9PYX-SxR_b0Bw6TszBroTYEoTQUixP47uDrN0s5j-bE85b-D7XpricmyzVA8C1heXixu46gkUhHtP01G6clDz6K9hg-F7P-2VrsAz0Ku8pz',
                'Content-Type: application/json'
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $result = curl_exec($ch);

            if ($result === FALSE) {
                Log::critical('Curl failed: ' . curl_error($ch));
            }

            curl_close($ch);
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    private function sendViaHuaweiPush($fields, $notifiable)
    {
        try {
            $type = strtolower($notifiable->userType->name ?? 'sr');
            $token = $this->fetchHuaweiBearerToken($type);
            if ($token === null) {
                throw new \Exception('Huawei Token Not found');
            }
            $url = 'https://push-api.cloud.huawei.com/v1/' . config('huawei.' . $type . '.app_id') . '/messages:send';
            $headers = array(
                'Authorization: Bearer ' . $token . '',
                'Content-Type: application/json; charset=UTF-8'
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildHuaweiBody($fields));

            $result = curl_exec($ch);

            if ($result === FALSE) {
                Log::critical('Curl failed: ' . curl_error($ch));
            }

            curl_close($ch);
        } catch
        (\Exception $e) {
            Log::error($e);
        }
    }

    private function fetchHuaweiBearerToken($type)
    {
        $str = null;
        try {
            $url = "https://oauth-login.cloud.huawei.com/oauth2/v3/token";
            $tokens = Cache::get('huawei_tokens');
            if (Cache::has('huawei_tokens') && array_key_exists($type, $tokens) && $tokens[$type]['expire_at'] > now()) {
                $str = $tokens[$type]['token'];
            } else {

                $data = [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('huawei.' . $type . '.app_id'),
                    'client_secret' => config('huawei.' . $type . '.secret')
                ];
                $token = Http::asForm()->post($url, $data)->json();

                Log::debug(json_encode($token));

                if (is_array($token) && array_key_exists('access_token', $token) && $token['access_token']) {
                    Cache::put('huawei_tokens', [
                        $type => [
                            'token' => $token['access_token'],
                            'expire_at' => now()->addSeconds($token['expires_in'])
                        ]
                    ]);
                    $str = $token['access_token'];
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception);
        }

        return $str;
    }

    private function buildHuaweiBody($fields): string
    {
        return '{
            "validate_only": false,
            "message": {
                "data": "{\"title\":\"' . $fields['title'] . '\",\"subtitle\":\"' . $fields['message'] . '\",\"action_url\":\"' . $fields['action_url'] . '\"}",
                "token": ["' . $fields['token'] . '"]
            }
        }';
    }
}
