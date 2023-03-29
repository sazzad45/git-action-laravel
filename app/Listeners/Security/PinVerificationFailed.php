<?php

namespace App\Listeners\Security;

use App\Constant\Security\BlockReason;
use App\Domain\Security\UserFailedActivity;
use App\Events\Security\PinVerificationFailedEvent;
use App\Notifications\AccountLocked;
use App\Traits\EnsureSecurityTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PinVerificationFailed
{
    use EnsureSecurityTrait;
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
     * @param  object  $event
     * @return void
     */
    public function handle(PinVerificationFailedEvent $event)
    {
        try {
            if (!empty($event->user)) {

                UserFailedActivity::create([
                    'user_id' => $event->user->id,
                    'feature' => BlockReason::Multiple_Failed_PIN_Verification_Attempt,
                    'payload' => $event->payload
                ]);

                $date = new \DateTime;
                $to_date = $date->format('Y-m-d H:i:s');
                $date->modify('-5 minutes');
                $from_date = $date->format('Y-m-d H:i:s');

                $totalFailedAttemptInLastFiveMinutes = UserFailedActivity::where('user_id', $event->user->id)
                    ->where('feature', BlockReason::Multiple_Failed_PIN_Verification_Attempt)
                    ->where('created_at', '>=', $from_date)
                    ->where('created_at', '<=', $to_date)
                    ->count();

                if ($totalFailedAttemptInLastFiveMinutes >= 3) {
                    $this->blockNow(
                        $event->user->mobile_no,
                        60,
                        BlockReason::Multiple_Failed_PIN_Verification_Attempt,
                        'Three(3) failed pin verification attempts in last five(5) minutes'
                    );

                    if ($event->user->email_verified) {
                        $event->user->notify(new AccountLocked($event->user->name, 'multiple failed pin verification attempts'));
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in PinVerificationFailed Listener: ');
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            Log::error($e);
        }
    }
}
