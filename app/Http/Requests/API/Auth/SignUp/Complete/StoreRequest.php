<?php

namespace App\Http\Requests\API\Auth\SignUp\Complete;

use App\Rules\API\Auth\SignUp\EmailTLD;
use App\Rules\API\Auth\SignUp\IsAgreed;
use App\Rules\API\Auth\SignUp\IsEmailVerified;
use App\Rules\API\Auth\SignUp\IsMobileNumberFirebaseVerified;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
                new IsEmailVerified($this->email ?? "")
            ],
            'uid' => [
                'required',
                'max:120',
                new IsMobileNumberFirebaseVerified($this->mobile_number ?? "")
            ],
            'accept'  => [
                'required',
                'in:0,1',
                new IsAgreed($this->accept ?? 0)
            ]
        ];
    }

    public function messages()
    {
        return [
            'password.regex' => trans('validation-ext.password_regex_failed'),
        ];
    }

}
