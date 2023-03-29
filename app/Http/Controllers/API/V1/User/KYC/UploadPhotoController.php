<?php

namespace App\Http\Controllers\API\V1\User\KYC;

use App\Domain\UserRelation\Models\UserProfile;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\User\KYC\UploadPhotoRequest;
use App\Traits\FileHandlerTrait;
use Illuminate\Support\Facades\Log;

class UploadPhotoController extends APIBaseController
{
    use FileHandlerTrait;

    public function upload(UploadPhotoRequest $request)
    {
        try {
            $user = auth()->user();

            $profile = UserProfile::where('user_id', $user->id)->first();

            $profile->update([
                'photo' => $this->processPhoto($request)
            ]);

            $this->logActivity('User profile photo uploaded', $user, $user, $profile->toArray());

            return $this->respondInJSON(200, [trans('messages.thanks_for_uploading_photo')]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}
