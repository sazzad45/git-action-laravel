<?php


namespace App\Http\Controllers\API\V1\Auth\PasswordChange;

use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Independent\Models\OTP;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\API\V1\Auth\PasswordChangeTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIBaseController;
use App\Domain\UserRelation\Models\PasswordHistory;
use App\Http\Requests\API\Auth\PasswordChange\Step2Request;

class PasswordChangeStep2Controller extends APIBaseController
{
    use PasswordChangeTrait;
    public function update(Step2Request $request)
    {
        try {


            $user = auth()->user();

            \DB::beginTransaction();


            $user->update([
                'password' => \Hash::make($request->input('password'))
            ]);
            $this->saveAsPasswordHistory($user->id, $user->password);

            \DB::statement("UPDATE oauth_access_tokens SET revoked = 1 WHERE user_id = $user->id");

            \DB::commit();

            $this->logActivity('Reset password using change password option', $user);

            return $this->respondInJSON(200, [trans('messages.password_change_success')]);

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
