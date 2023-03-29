<?php

namespace App\Http\Requests\API\Wallet\Transaction;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            'invoice_id' => 'required'
        ];
    }
}
