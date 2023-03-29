<?php

namespace App\Http\Requests\API\User\Referral;

use App\Rules\API\Verify\VerifyReferralCode;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class SubmitRequest extends FormRequest
{
    use ApiValidationResponseHelperTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $user = auth()->user();
        $code = $request->input('code') ?? '';
        return [
            'code' =>[
                'required',
                new VerifyReferralCode($code, $user)
            ]

        ];
    }
}
