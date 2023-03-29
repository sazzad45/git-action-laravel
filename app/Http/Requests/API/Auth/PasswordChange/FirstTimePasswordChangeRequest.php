<?php

namespace App\Http\Requests\API\Auth\PasswordChange;

use App\Rules\API\Validate\FirstTimePasswordChangeEligible;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class FirstTimePasswordChangeRequest extends FormRequest
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
            'password' => [
                'required',
                'between:8,32',
                'confirmed',
                (new FirstTimePasswordChangeEligible($user))
            ]
        ];
    }
}
