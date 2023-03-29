<?php

namespace App\Jobs;

use App\Domain\Independent\Models\OTP;
use App\Notifications\SendOTPOverMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OTPForPasswordChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $otp;
    private $purpose;
    private $email;
    private $mobile_number;
    private $client;
    private $ip_address;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $mobile_number
     * @param int $otp
     * @param string $purpose
     * @param string $client
     * @param string $ip_address
     */
    public function __construct(string $email, string $mobile_number, int $otp, string $purpose, string $client, string $ip_address)
    {
        $this->otp = $otp;
        $this->purpose = $purpose;
        $this->email = $email;
        $this->mobile_number = $mobile_number;
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
            'identity' => $this->mobile_number,
            'otp' => $this->otp,
            'purpose' => $this->purpose,
            'client' => $this->client,
            'ip_address' => $this->ip_address
        ]);

        // if $this->mobile_number then send SMS

        if ($this->email)
        {
            $subject = "OTP for Password Change";
            $message = "Your OTP for Password Change : {$this->otp}";
            \Notification::route('mail', $this->email)
                ->notify(new SendOTPOverMail($message, $subject));
        }
    }
}
