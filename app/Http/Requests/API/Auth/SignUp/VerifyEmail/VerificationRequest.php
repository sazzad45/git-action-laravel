<?php

namespace App\Http\Requests\API\Auth\SignUp\VerifyEmail;

use App\Rules\API\Auth\SignUp\EmailTLD;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
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
                'required',
                'numeric',
                'regex:/[+][9][6][4](0)?[0-9]{9,10}$/',
                'unique:users,mobile_no'
            ],
            'first_name' => [
                'required',
                'max:48'
            ],
            'last_name' => [
                'required',
                'max:48'
            ],
            'email' => [
                'required',
                'email',
                'max:80',
                'unique:users,email',
                new EmailTLD
            ],
            'password' => [
                'required',
                'between:8,32',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\d)(?=.*[$%&_+-.@#]).+$/',
                'confirmed'
            ],
            'password_confirmation' => [
                'required',
                'between:8,32'
            ],
            'email_otp' => [
                'required',
                'digits:6',
            ]
        ];
    }
}
