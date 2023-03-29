<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class DecryptDataPayload
{
    public function handle($request, Closure $next)
    {
        try {
            if($request->has('data') && !empty($request->input('data'))) {
                $decryptDataPayload = $this->_decrypt_string($request->input('data'));
                return $next($request->merge(json_decode($decryptDataPayload, true)));
            }
            return $next($request);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ApiResponseTrait::exceptionResponse(trans('messages.internal_server_error'));
        }
    }

    protected  function _decrypt_string($data="")
    {
        list($iv, $encrypted_data) = explode('||', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, "aes-256-cbc", 'G7RAi4BTpa32H1ykg56LkrjqTBoEYqCc', 0, $iv);
    }
}
