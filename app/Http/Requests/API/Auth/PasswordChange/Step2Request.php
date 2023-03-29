<?php


namespace App\Http\Requests\API\Auth\PasswordChange;


use App\Domain\API\Utility\OTPPurpose;
use App\Rules\API\Verify\VerifyOldPassword;
use App\Rules\API\Verify\VerifyOTP;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class Step2Request extends FormRequest
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
        $user = auth()->user();

        $identifier = $user->mobile_no;
        $email = $user->email; 
        $purpose = OTPPurpose::PASSWORD_CHANGE;

        return [
            'old_password' =>[
                'required',
                (new VerifyOldPassword($user))
            ],
            'password' => [
                'required',
                'between:8,32'
            ],
            'otp' => [
                'required',
                'digits:6',
                (new VerifyOTP($identifier, $purpose, $email))
            ],
        ];
    }
}
