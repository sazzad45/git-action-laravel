<?php

namespace App\Http\Requests\API\User\Firebase;

use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;

class FirebaseTokenUpdateRequest extends FormRequest
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
            'fcm_key'=> 'required',
            'push_platform' => 'required',
            'push_os' => 'required',
            'push_cur_version' => 'required',
        ];
    }
}
