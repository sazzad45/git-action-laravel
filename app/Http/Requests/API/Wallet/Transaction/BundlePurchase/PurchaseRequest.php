<?php

namespace App\Http\Requests\API\Wallet\Transaction\BundlePurchase;

use App\Rules\API\Verify\VerifyPIN;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'operator_id' => 'required|integer',
            'bundle_id' => 'required',
            'pin' => [
                'required',
                'digits:4',
                (new VerifyPIN($this->user()))
            ],
        ];
    }
}
