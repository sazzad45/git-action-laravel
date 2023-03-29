<?php

namespace App\Listeners\Security;

use App\Domain\Security\UserAction;
use App\Events\Security\UserActionEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserActionListener
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
     * @param  UserActionEvent  $event
     * @return void
     */
    public function handle(UserActionEvent $event)
    {
        try {
            UserAction::create([
                'user_id' => $event->user->id,
                'action' => $event->action,
                'ip_address' => $event->ip_address,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in User Action Listener: ');
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            Log::error($e);
        }
    }
}
