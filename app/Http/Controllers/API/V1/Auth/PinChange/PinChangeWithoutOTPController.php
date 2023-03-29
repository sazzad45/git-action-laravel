<?php


namespace App\Http\Controllers\API\V1\Auth\PinChange;


use App\Domain\FastPay\Constant\APIEndPoints;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Auth\PinChange\PinChangeWithoutOTPRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PinChangeWithoutOTPController extends APIBaseController
{
    public function update(PinChangeWithoutOTPRequest $request)
    {
        try {

            $user = auth()->user();

            \DB::beginTransaction();

            $user->update([
                'pin' => \Hash::make($request->input('pin'))
            ]);

            /** Removed Old System Connection */
            // try {
            //     $this->update_on_old_system($request->input('pin'));
            // }catch (\Exception $exception){
            //     return $this->respondInJSON(500, [$exception->getMessage()]);
            // }
            \DB::commit();

            $this->logActivity('Pin changed without otp', $user);

            return $this->respondInJSON(200, [trans('messages.pin_update_success')], null);
        } catch(\Exception $e) {
            \DB::rollback();

            \Log::error($e);
            \Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    public function update_on_old_system($pin)
    {

        $response = Http::
            timeout(config('internal_services.request_timeout'))
            ->withToken(Cache::get(auth()->user()->mobile_no.'_old_token'))
            ->post(APIEndPoints::PIN_SET,[
                'pin' => $pin,
                'mobile_no' => auth()->user()->mobile_no,
                'device_id' => 'RS',
                'type' => 'B'
            ]);

        Log::info($response->body());

        if($response->failed() || !isset($response->json()['code']) || $response->json()['code'] != 200){
            throw new \Exception(isset($response->json()["messages"])  ?
                implode(' ',$response->json()["messages"])  :
                trans('internal_server_error')
            );
        }
        return true;
    }
}
