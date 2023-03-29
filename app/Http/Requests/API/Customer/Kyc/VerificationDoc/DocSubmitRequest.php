<?php

namespace App\Http\Requests\API\Customer\Kyc\VerificationDoc;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class DocSubmitRequest extends FormRequest
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
            'kyc_id' => 'required|exists:user_verification_docs,id',
            'doc_type_id' => 'required|exists:user_verification_doc_types,id',
            'doc_number' => 'required',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'full_name' => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required|in:0,1,2,9',
            'issue_date' => 'required',
            'expiry_date' => 'required',
            'monthly_income' => 'required|numeric',
            'document_type_ids' => 'array',
            'document_images' => 'array',
            'document_images.*' => 'file'
        ];
    }
}
