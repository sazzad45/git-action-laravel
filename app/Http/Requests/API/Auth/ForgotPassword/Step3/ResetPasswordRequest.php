<?php

namespace App\Http\Requests\API\Auth\ForgotPassword\Step3;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
                'required_without_all:email',
                'numeric',
                'regex:/[+][9][6][4](0)?[0-9]{9,10}$/',
                'exists:users,mobile_no'
            ],
            'email' => [
                'required_without_all:mobile_number',
                'email',
                'max:80',
                'exists:users,email'
            ],
            'otp' => [
                'required',
                'digits:6'
            ],
            'password' => [
                'required',
                'between:8,32',
                'confirmed'
            ],
            'password_confirmation' => [
               'required',
               'between:8,32'
            ]
        ];
    }
}
