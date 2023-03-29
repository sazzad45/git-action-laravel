<?php

namespace App\Listeners\Security;

use App\Constant\Security\BlockReason;
use App\Domain\Security\UserFailedActivity;
use App\Events\Security\OTPVerificationFailedEvent;
use App\Notifications\AccountLocked;
use App\Traits\EnsureSecurityTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class OTPVerificationFailed
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
    public function handle(OTPVerificationFailedEvent $event)
    {
        try {
            if (!empty($event->user)) {

                UserFailedActivity::create([
                    'user_id' => $event->user->id,
                    'feature' => BlockReason::Too_Many_OTP_Generated,
                    'payload' => $event->payload
                ]);

                $date = new \DateTime;
                $to_date = $date->format('Y-m-d H:i:s');
                $date->modify('-5 minutes');
                $from_date = $date->format('Y-m-d H:i:s');

                $totalFailedAttemptInLastFiveMinutes = UserFailedActivity::where('user_id', $event->user->id)
                    ->where('feature', BlockReason::Too_Many_OTP_Generated)
                    ->where('created_at', '>=', $from_date)
                    ->where('created_at', '<=', $to_date)
                    ->count();

                if ($totalFailedAttemptInLastFiveMinutes >= 5) {
                    $this->blockNow(
                        $event->user->mobile_no,
                        60,
                        BlockReason::Too_Many_OTP_Generated,
                        'Five(5) failed OTP Generated in last five(5) minutes'
                    );

                    if ($event->user->email_verified) {
                        $event->user->notify(new AccountLocked($event->user->name, 'multiple failed OTP verification attempts'));
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in OTPVerificationFailed Listener: ');
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            Log::error($e);
        }
    }
}
