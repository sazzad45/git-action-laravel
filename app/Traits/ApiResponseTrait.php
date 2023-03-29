<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ApiResponseTrait
{
    public function exceptionResponse(string $exception)
    {
        return response()->json([
            'code'      =>  500,
            'messages'  =>  [ $exception ],
            'data'      =>  null
        ], 200);
    }

    public function invalidResponse(array $messages)
    {
        return response()->json([
            'code'      =>  422,
            'messages'  =>  $messages,
            'data'      =>  null
        ], 200);
    }

    public function successResponse(array $messages, $data = null)
    {
        return response()->json([
            'code'      =>  200,
            'messages'  =>  $messages,
            'data'      =>  $data
        ], 200);
    }

    public function respondWithAccessToken($accessToken)
    {
        return response()->json([
            'code'      =>  200,
            'messages'  =>  [],
            'data'      =>  $accessToken
        ], 200);
    }

    public function unauthorizedResponse(array $messages, int $code = 401)
    {
        return response()->json([
            'code'      =>  $code,
            'messages'  =>  $messages,
            'data'      =>  ""
        ], 200);
    }

    public function _encrypt_string($data="")
    {
        $iv = Str::random(16);
        $encrypted = openssl_encrypt($data, "aes-256-cbc", 'G7RAi4BFastSolutionLkrjqTBoEYqCc', 0, $iv);

        return base64_encode($iv . '||' . $encrypted);
    }

    protected  function _decrypt_string($data="")
    {
        list($iv, $encrypted_data) = explode('||', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, "aes-256-cbc", 'G7RAi4BFastSolutionLkrjqTBoEYqCc', 0, $iv);
    }

    public function middlewareResponse(array $messages, $middleware)
    {
        return response()->json([
            'code'      =>  900,
            'messages'  =>  $messages,
            'middleware'      =>  $middleware
        ], 200);
    }

    public function respondInJSON(int $code, array $messages = [], $data = null)
    {
        return response()->json([
            'code' => $code,
            'messages' => $messages,
            'data' => $data
        ]);
    }

    public function respondInJSONWithAdditional(int $code, array $messages = [], $data = null, $per_page, $total)
    {
        return response()->json([
            'code' => $code,
            'messages' => $messages,
            'data' => $data,
            'data_additional' => [
                'per_page' => $per_page,
                'total' => $total,
            ]
        ]);
    }

    public function _encrypt_string_with_prefix($data="")
    {
        $data = "PayMe$." . $data;

        $iv = Str::random(16);
        $encrypted = openssl_encrypt($data, "aes-256-cbc", 'U2geM25MbSvltsfQwAsVg3rA1QVMnRXp', 0, $iv);

        return base64_encode($iv . '||' . $encrypted);
    }

    protected  function _decrypt_string_with_prefix($data="")
    {
        list($iv, $encrypted_data) = explode('||', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, "aes-256-cbc", 'U2geM25MbSvltsfQwAsVg3rA1QVMnRXp', 0, $iv);
    }



}
