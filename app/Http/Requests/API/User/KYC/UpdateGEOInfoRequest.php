<?php

namespace App\Http\Requests\API\User\KYC;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGEOInfoRequest extends FormRequest
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
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id'
            ],
            'address_line1' => [
                'required',
                'max:120'
            ]
        ];
    }
}
