<?php

namespace App\Http\Controllers\API\V1\Auth\PasswordChange;

use App\Domain\UserRelation\Models\PasswordHistory;
use App\Http\Controllers\API\V1\Auth\PasswordChangeTrait;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\PasswordChange\FirstTimePasswordChangeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FirstLoginPasswordChangeController extends APIBaseController
{
    use PasswordChangeTrait;

    public function change(FirstTimePasswordChangeRequest $request)
    {
        try {

            $user = auth()->user();

            DB::beginTransaction();

            /** Removed Old System Connection */
            // try {
            //     $this->update_on_old_system($request);
            // }catch (\Exception $exception){
            //     return $this->respondInJSON(500, [$exception->getMessage()]);
            // }

            $user->update([
                'password' => Hash::make($request->input('password')),
                'is_first_login' => 1
            ]);
            $this->saveAsPasswordHistory($user->id, $user->password);

            \DB::statement("UPDATE oauth_access_tokens SET revoked = 1 WHERE user_id = $user->id");

            \DB::commit();

            $this->logActivity('First login password changed successfully', $user);

            return $this->respondInJSON(200, [trans('messages.password_change_success')]);

        } catch(\Exception $e) {
            \DB::rollback();

            \Log::error($e);
            \Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

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
