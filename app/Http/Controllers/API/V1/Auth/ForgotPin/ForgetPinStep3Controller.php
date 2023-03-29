<?php

namespace App\Http\Controllers\API\V1\Auth\ForgotPin;

use App\Domain\UserRelation\Models\PasswordHistory;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\API\V1\Auth\PasswordChangeTrait;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\ForgotPassword\Step3\ResetPasswordRequest;
use App\Http\Requests\API\Auth\ForgotPin\Step3\ResetPinRequest;
use Illuminate\Support\Facades\Log;


class ForgetPinStep3Controller extends APIBaseController
{
    public function setPassword(ResetPinRequest $request)
    {
        if($request->has('mobile_number'))
            $user = User::where('mobile_no', $request->input('mobile_number'))
                ->where('status', 1);
        else
            $user = User::where('email', $request->input('email'))
                ->where('status', 1);

        try {
            if($user->count())
            {
                $user = $user->first();
                $user->update([
                    'pin' => \Hash::make($request->input('pin'))
                ]);
                return $this->respondInJSON(200, [trans('messages.password_reset_success')]);
            }

            return $this->respondInJSON(422, [trans('messages.internal_server_error')]);

        } catch(\Exception $e) {
            \DB::rollback();

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    protected function saveAsPasswordHistory($userID, $updatedPassword): void
    {
        PasswordHistory::create([
            'user_id'   =>  $userID,
            'password'  =>  $updatedPassword
        ]);
    }
}
