<?php

namespace App\Notifications\Transactions\BundlePurchase;

use App\Domain\Transaction\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Received extends Notification
{
    use Queueable;
    /**
     * @var array
     */
    private $channels;

    /**
     * @var
     */
    private $descriptionMessage;

    /**
     * Create a new notification instance.
     *
     * @param array $channels
     * @param Statement $statement
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
            ->subject('Bundle Purchase via FastPay Wallet')
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
            "title" => "Bundle Purchase",
            "sub_title" => "Bundle Purchase via FastPay Wallet",
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
            "title" => "Bundle Purchase",
            "description" => $this->descriptionMessage,
        ];
    }
}
