<?php


namespace App\Traits;


use App\Domain\Independent\Models\EmailVerificationToken;
use App\Domain\UserRelation\Models\User;
use App\Notifications\SendVerificationEmail;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait EmailVerificationTrait
{
    /**
     * Sending Verification Email
     *
     * @param Request $request
     * @param User $user
     */
    protected function sendVerificationEmail(Request $request, User $user)
    {
        try {

            if( $request->has('email') ) {

                $token = Str::random(120);

                EmailVerificationToken::create([
                    'email' =>  $user->email,
                    'token' =>  $token
                ]);

                $user->notify(
                    (new SendVerificationEmail($request, $token))
                        ->delay(Carbon::now()->addSeconds(5))
                );
            }

        } catch(\Exception $e) {
            \Log::error($e);
            \Log::error($e->getFile(). ' '. $e->getLine(). ' '. $e->getMessage());
        }
    }
}
