<?php


namespace App\Http\Requests\API\Auth\PasswordChange;


use App\Rules\API\Verify\VerifyOldPassword;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class Step1Request extends FormRequest
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

        return [
            'old_password' => [
                'required',
                'between:8,32',
                (new VerifyOldPassword($user))
            ],
            'password' => [
                'required',
                'between:8,32',
                'confirmed'
            ]
        ];
    }
}
