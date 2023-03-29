<?php


namespace App\Http\Requests\API\Auth\PinChange;


use App\Domain\API\Utility\OTPPurpose;
use App\Rules\API\Verify\VerifyOTP;
use App\Rules\API\Verify\VerifyPIN;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $purpose = OTPPurpose::PIN_CHANGE;

        return [
            'old_pin' => [
                'required',
                'integer',
                'digits:4',
                (new VerifyPIN($user))
            ],
            'pin' => [
                'required',
                'integer',
                'digits:4',
                'confirmed'
            ],
            'otp' => [
                'required',
//                'integer',
//                'digits:6',
                (new VerifyOTP($identifier, $purpose, $email))
            ]
        ];
    }
}
