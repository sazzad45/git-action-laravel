<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SuccessfulLogin extends Notification implements ShouldQueue
{
    use Queueable;

    protected $client;
    protected $ip;
    protected $mobileNumber;


    public function __construct(Request $request, $mobileNumber)
    {
        $this->client = $request->header('User-Agent');
        $this->ip = trim(explode(',', $request->header('X-Forwarded-For'))[0]);
        $this->mobileNumber = $mobileNumber;
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
        $url = url('/');

        return (new MailMessage)
                    ->success()
                    ->subject('Notification: Successful account login.')
                    ->greeting('Login Alert!')
                    ->line('Mobile No. '.$this->mobileNumber)
                    ->line('Successful login detected from:')
                    ->line($this->client)
                    ->line('IP Address: '.$this->ip)
                    ->line('At: '.date('d-m-Y H:i:s'))
                    ->line('Thank you for using');
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
