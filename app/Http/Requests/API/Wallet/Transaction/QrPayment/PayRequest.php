<?php

namespace App\Http\Requests\API\Wallet\Transaction\QrPayment;

use App\Rules\API\Verify\VerifyPIN;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class PayRequest extends FormRequest
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
            'qr_text' => 'required|string',
            'amount' => 'required|integer|min:250',
            'pin' => [
                'required',
                'digits:4',
                (new VerifyPIN($user))
            ],
        ];
    }
}
