<?php

namespace App\Jobs;

use App\Domain\Independent\Models\OTP;
use App\Notifications\SignUpEmailVerificationByOTP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOTPViaEmailInSignUp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $otp;
    private $purpose;
    private $email;
    private $client;
    private $ip_address;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param int $otp
     * @param string $purpose
     * @param string $client
     * @param string $ip_address
     */
    public function __construct(string $email, int $otp, string $purpose, string $client, string $ip_address)
    {
        $this->otp = $otp;
        $this->purpose = $purpose;
        $this->email = $email;
        $this->client = $client;
        $this->ip_address = $ip_address;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        OTP::create([
            'identity' => $this->email,
            'otp' => $this->otp,
            'purpose' => $this->purpose,
            'client' => $this->client,
            'ip_address' => $this->ip_address
        ]);

        Log::info("Your {$this->purpose} OTP is : {$this->otp}");
    }
}
