<?php


namespace App\Channels;

use App\Domain\System\SMSManager;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
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
        try {
            $message = $notification->toSms($notifiable);
            $smsManager = new SMSManager($notifiable->mobile_no, $message['message'], false, $message['sms_gateway']);
            $smsManager->send();
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
