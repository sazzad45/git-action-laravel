<?php

namespace App\Events\Security;

use App\Domain\UserRelation\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OTPVerificationFailedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public $payload;
    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user, $payload)
    {
        $this->user = $user;
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
