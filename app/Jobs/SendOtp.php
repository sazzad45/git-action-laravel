<?php

namespace App\Jobs;

use App\Domain\UserRelation\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtp implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, SmsApi;

    protected $user;
    protected $message;

    /**
     * Create a new job instance.
     * @param User $user
     * @param $message
     * @return void
     */
    public function __construct(User $user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $this->sendCustomMessage($this->user->mobile_no, $this->message);

        } catch(\Exception $e) {
            \Log::error($e->getFile(). ' '. $e->getLine(). ' '. $e->getMessage());
        }
    }
}
