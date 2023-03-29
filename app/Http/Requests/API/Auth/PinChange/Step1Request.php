<?php


namespace App\Http\Requests\API\Auth\PinChange;


use App\Rules\API\Verify\VerifyPIN;
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
        ];
    }
}
