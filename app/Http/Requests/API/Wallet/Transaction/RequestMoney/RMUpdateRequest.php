<?php

namespace App\Http\Requests\API\Wallet\Transaction\RequestMoney;


use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class RMUpdateRequest extends FormRequest
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
            'request_id' => [
                'required',
                'exists:money_requests,id'
            ],
            'status' => [
                'required',
                'in:1,2'
            ],
            'invoice_id' => [
                'nullable',
                'exists:transactions,tx_unique_id'
            ]
        ];
    }
}
