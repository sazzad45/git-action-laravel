<?php

namespace App\Notifications\Kyc;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationDocSubmitted extends Notification
{
    use Queueable;

    /**
     * @var array
     */
    private $channels;
    private $title;
    private $message;

    /**
     * Create a new notification instance.
     *
     * @param array $channels
     * @param string $title
     * @param string $message
     */
    public function __construct(array $channels, string $title, string $message)
    {
        $this->channels = $channels;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            "title" => "KYC Verification Update",
            "sub_title" => $this->title,
            "title_color" => "#03EBA3",
            "icon" => "https://revamp.fast-pay.cash/image/icons/send_money.png",
            "description" => $this->message,
            "created_at" => now()->diffForHumans(),
            "jump_to" => null
        ];
    }

    /**
     * Get the fcm push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFcmPush($notifiable)
    {
        return [
            "title" => $this->title,
            "description" => $this->message,
        ];
    }
}
