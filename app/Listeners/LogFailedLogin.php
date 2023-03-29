<?php

namespace App\Listeners;

use App\Constant\Security\BlockReason;
use App\Domain\Security\FailedLoginAttempt;
use App\Models\UserRelation\User;
use App\Notifications\AccountLocked;
use App\Traits\EnsureSecurityTrait;
use Illuminate\Auth\Events\Failed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LogFailedLogin
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
     * @param Failed $event
     * @return void
     */
    public function handle(Failed $event)
    {
        try {
            if (!empty($event->credentials['mobile_no'])) {
                if (!empty($event->user)) {

                    FailedLoginAttempt::create(['mobile_no' => $event->user->mobile_no]);

                    $date = new \DateTime;
                    $to_date = $date->format('Y-m-d H:i:s');
                    $date->modify('-5 minutes');
                    $from_date = $date->format('Y-m-d H:i:s');

                    $totalFailedAttemptInLastFiveMinutes = FailedLoginAttempt::whereMobileNo($event->user->mobile_no)
                        //->whereRaw("created_at > date_sub(now(), interval 5 minute)")
                        ->where('created_at', '>=', $from_date)
                        ->where('created_at', '<=', $to_date)
                        ->count();

                    if ($totalFailedAttemptInLastFiveMinutes >= 5) {

                        $this->blockNow(
                            $event->user->mobile_no,
                            60,
                            BlockReason::Multiple_Failed_Login_Attempt,
                            'Five(5) failed login attempts in last five(5) minutes'
                        );

                        DB::statement("DELETE FROM oauth_access_tokens WHERE user_id = {$event->user->id}");

                        if (!is_null($event->user->is_key_verified)) {
                            if ($event->user->email_verified) {
                                $event->user->notify(new AccountLocked($event->user->name));
                            }
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in LogFailedLogin Listener: ');
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
        }
    }
}
