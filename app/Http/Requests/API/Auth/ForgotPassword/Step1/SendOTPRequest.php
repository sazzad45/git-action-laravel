<?php

namespace App\Http\Requests\API\Auth\ForgotPassword\Step1;

use App\Rules\API\Auth\ForgotPassword\EmailPhoneNoForSamePerson;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class SendOTPRequest extends FormRequest
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
        //$mobile_no = $this->input('mobile_no') ?: '';

        return [
            'mobile_number' =>[
                'required_without_all:email',
                'numeric',
                'regex:/[+][9][6][4](0)?[0-9]{9,10}$/',
                'exists:users,mobile_no'
            ],
            'email' => [
                'required_without_all:mobile_number',
                'email',
                'max:80',
                'exists:users,email',
//                new EmailPhoneNoForSamePerson($mobile_no)
            ]
        ];
    }
}
