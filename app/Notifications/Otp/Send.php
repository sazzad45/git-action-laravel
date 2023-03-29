<?php

namespace App\Notifications\Otp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Send extends Notification
{
    use Queueable;

    private $channels;
    private $smsGateway;
    private $message;
    private $subject;

    /**
     * Create a new notification instance.
     *
     * @param array $channels
     * @param string $descriptionMessage
     */
    public function __construct(array $channels, string $smsGateway, string $message, string $subject = 'OTP for Reset Password')
    {
        $this->channels = $channels;
        $this->smsGateway = $smsGateway;
        $this->message = $message;
        $this->subject = $subject;
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
            ->subject($this->subject)
            ->line($this->message)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the sms representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toSms($notifiable)
    {
        return [
            "message" => $this->message,
            "sms_gateway" => $this->smsGateway,
        ];
    }
}
