<?php

namespace App\Http\Controllers\API\V1\User\Firebase;

use App\Domain\FastPay\Constant\APIEndPoints;
use App\Domain\UserRelation\Models\UserDevice;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\User\Firebase\FirebaseTokenUpdateRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseTokenUpdateController extends APIBaseController
{
    public function update(FirebaseTokenUpdateRequest $request)
    {
        try {
            $user = auth()->user();

            $userDevice = new UserDevice();
            $userDevice->user_id = $user->id;
            $userDevice->fcm_key = $request->fcm_key;
            $userDevice->push_cur_version = $request->push_cur_version;
            $userDevice->push_handset_model = $request->push_handset_model;
            $userDevice->push_imei = $request->push_imei;
            $userDevice->push_platform = $request->push_platform;
            $userDevice->push_os = $request->push_os;
            $userDevice->push_os_version = $request->push_os_version;
            $userDevice->save();

            $update_array = ['fcm_key' => $request->input('fcm_key') ];
            $user->update($update_array);

            /** Removed Old System Connection */
            // $this->update_on_old_system($update_array);

            $this->logActivity('Firebase token updated', $user, $user, $userDevice->toArray());

            return $this->respondInJSON(200, [], null);
        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
    public function update_on_old_system($update_array)
    {
        if(config('internal_services.fastpay_api_old')){
            Http::
//            withOptions([
//                    'proxy' => config('internal_services.proxy_url')
//                ])
//                ->
                timeout(config('internal_services.request_timeout'))
                ->withToken(Cache::get(auth()->user()->mobile_no.'_old_token'))
                ->post(APIEndPoints::FCM_TOKEN_UPDATE,$update_array);
        }

    }
}
