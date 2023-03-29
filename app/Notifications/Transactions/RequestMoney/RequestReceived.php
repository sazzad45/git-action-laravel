<?php

namespace App\Notifications\Transactions\RequestMoney;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestReceived extends Notification
{
    use Queueable;
    /**
     * @var array
     */
    private $channels;

    private $descriptionMessage;

    private $actionUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $channels, string $descriptionMessage, string $action_url = '')
    {
        $this->channels = $channels;
        $this->descriptionMessage = $descriptionMessage;
        $this->actionUrl = $action_url;
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
            ->subject('New Request Money')
            ->line($this->descriptionMessage)
            // ->action('Login To FastPay', url('/'))
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
            "title" => "New Request Money",
            "sub_title" => "You have a new money request",
            "title_color" => "#03EBA3",
            "icon" => "https://revamp.fast-pay.cash/image/icons/receive.png",
            "description" => $this->descriptionMessage,
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
            "title" => "New Request Money",
            "description" => $this->descriptionMessage,
            "action_url" => $this->actionUrl
        ];
    }
}
