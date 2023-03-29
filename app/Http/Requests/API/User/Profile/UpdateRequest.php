<?php


namespace App\Http\Requests\API\User\Profile;


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
        return [
            'full_name' => 'required',
            'surname' => 'required',
            'date_of_birth' => 'required|date',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'address_line1' => 'required'
        ];
    }
}
