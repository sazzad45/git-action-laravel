<?php

namespace App\Http\Requests\API\Auth\SignUp\Firebase;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class VerificationInformationRequest extends FormRequest
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
            'uid' => 'required|max:120'
        ];
    }
}
