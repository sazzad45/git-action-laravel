<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationEmail extends Notification
{
    use Queueable;

    private $email;
    private $name;
    private $token;

    /**
     * Create a new notification instance.
     * @param Request $request
     * @param String $token
     *
     * @return void
     */
    public function __construct(Request $request, $token)
    {
        $this->name = $request->input('first_name'). ' '. $request->input('last_name');
        $this->email = $request->input('email');
        $this->token = $token;
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
        $url = route('verify.token.email', [urlencode($this->token), urlencode($this->email)]);

        return (new MailMessage)
            ->success()
            ->subject(config('agentApp.name') . ' | Email Verification')
            ->greeting('Dear '.$this->name .',')
            ->line('Please verify your email address by clicking the link given below-')
            ->action('Click To Verify Email!', $url)
            ->line('Thank you for using '. config('agentApp.name'));
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
