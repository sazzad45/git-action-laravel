<?php

namespace App\Jobs\Otp;

use App\Domain\UserRelation\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Independent\Models\OTP;
use App\Notifications\Otp\Send;
use Illuminate\Support\Str;

class OtpGenerator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Request $request;
    private User $user;
    private string $purpose;
    private array $channels;
    private string $smsGateway;
    private string $message;
    private string $mailSubject;
    private int $otp;
    private string $client;
    private string $ip_address;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param User $user
     * @param string $purpose
     * @param string $channels
     * @param string $message
     */
    public function __construct(Request $request, User $user, string $purpose, array $channels = ['mail'], $smsGateway = 'SMSGlobal', string $message = '', string $mailSubject = '')
    {
        $this->request = $request;
        $this->user = $user;
        $this->purpose = $purpose;
        $this->channels = $channels;
        $this->smsGateway = $smsGateway;
        $this->otp = $this->getOtp();
        $this->message = $message;
        $this->mailSubject = $mailSubject;
        $this->client = substr($request->header('User-Agent'), 0, 80);
        $this->ip_address = trim(explode(',', $request->header('X-Forwarded-For'))[0]);
    }

    public function uniqueId(): string
    {
        return (string) Str::uuid() . '_' . '_' . mt_rand(1111111, 9999999);
    }

    public function backoff(): array
    {
        return [1, 15, 45];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        OTP::create([
            'identity' => $this->user->mobile_no,
            'otp' => $this->otp,
            'purpose' => $this->purpose,
            'client' => $this->request,
            'ip_address' => $this->ip_address
        ]);

        $this->user->notify(new Send($this->channels, $this->smsGateway, $this->getMessage(), $this->getMailSubject()));
    }

    private function getOtp()
    {
        $otp = 123456;

        if(config('basic_settings.live_otp') == 1){
            $otp = random_int(100000, 999999);
        }

        return $otp;
    }

    private function getMessage()
    {
        $message = $this->message;
        if ( ! $message ) {
            $message = "Your OTP for {$this->purpose} : {$this->otp}";
        }

        return $message;
    }

    private function getMailSubject()
    {
        $mailSubject = $this->mailSubject;
        if ( !$mailSubject ) {
            $mailSubject = "OTP for {$this->purpose}";
        }

        return $mailSubject;
    }
}
