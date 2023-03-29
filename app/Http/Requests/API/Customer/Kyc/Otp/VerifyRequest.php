<?php

namespace App\Http\Requests\API\Customer\Kyc\Otp;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
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
    public function rules()
    {
        return [
            'mobile_number' => [
                'required_without_all:qr_text',
                'numeric',
                'regex:/[+][9][6][4](0)?[0-9]{9,10}$/',
                'exists:users,mobile_no'
            ],
            'qr_text' => 'required_without_all:mobile_number',
            'otp' => [
                'required',
                'digits:6'
            ],
        ];
    }
}
