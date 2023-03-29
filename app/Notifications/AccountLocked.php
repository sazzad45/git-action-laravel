<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AccountLocked extends Notification implements ShouldQueue
{
    use Queueable;

    private $name;
    private $reason;

    /**
     * Create a new notification instance.
     *
     * @param $name
     * @param string $reason
     */
    public function __construct($name, $reason = "multiple failed login attempts")
    {
        $this->name = $name;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->subject('FastPay Account Get Locked!')
                    ->greeting('Dear ' . $this->name . ',')
                    ->line('We are sorry to inform you that recently there were '.$this->reason.' in your account. To protect your account we locked your account for the next 60 minutes. If you need any help please contact our support.')
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
            //
        ];
    }
}
