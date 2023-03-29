<?php

namespace App\Traits;
use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait ApiValidationResponseHelperTrait
{
    use ApiResponseTrait;

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code'  =>  422,
            'messages' => $validator->errors()->all(),
            'data'  => null
        ], 200));
    }
}
