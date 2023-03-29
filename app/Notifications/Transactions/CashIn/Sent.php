<?php

namespace App\Notifications\Transactions\CashIn;

use App\Domain\Transaction\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Sent extends Notification
{
    use Queueable;
    /**
     * @var array
     */
    private $channels;

    private $descriptionMessage;

    /**
     * Create a new notification instance.
     *
     * @param array $channels
     * @param string $descriptionMessage
     */
    public function __construct(array $channels, string $descriptionMessage)
    {
        $this->channels = $channels;
        $this->descriptionMessage = $descriptionMessage;
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
                    ->subject('Money Sent Successfully')
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
            "title" => "Money Sent",
            "sub_title" => "Money Sent Successfully",
            "title_color" => "#03EBA3",
            "icon" => "https://revamp.fast-pay.cash/image/icons/send_money.png",
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
            "title" => "Money Sent",
            "description" => $this->descriptionMessage,
        ];
    }
}
