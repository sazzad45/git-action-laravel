<?php

namespace App\Listeners;


use App\Notifications\AccountLocked;
use App\Notifications\SuccessfulLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Request as RequestedClient;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        if($event->user->is_kyc_verified == 1) {
            if($event->user->email_verified) {
                $event->user->notify( new SuccessfulLogin( request(), $event->user->mobile_no));
            }
        }
    }
}
