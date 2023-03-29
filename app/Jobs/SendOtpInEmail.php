<?php

namespace App\Jobs;

use App\Domain\Independent\Models\OTP;
use App\Notifications\SendOtpForEmailChange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtpInEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mobileNumber;
    private $otp;
    private $purpose;
    private $email;

    /**
     * Create a new job instance.
     *
     * @param $mobileNumber
     * @param $email
     * @param $otp
     * @param $purpose
     */
    public function __construct($mobileNumber, $email, $otp, $purpose)
    {
        $this->mobileNumber = $mobileNumber;
        $this->otp = $otp;
        $this->purpose = $purpose;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        OTP::create([
            'identity' => $this->mobileNumber,
            'otp' => $this->otp,
            'purpose' => $this->purpose
        ]);

        \Notification::route('mail', $this->email)
            ->notify(new SendOtpForEmailChange("Your OTP for Email Update is : {$this->otp}"));
    }
}
