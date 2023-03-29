<?php

namespace App\Http\Requests\API\Wallet\Transaction\RequestMoney;

use App\Rules\API\Verify\VerifyPIN;
use App\Traits\ApiValidationResponseHelperTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ExecuteRequest extends FormRequest
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

        $rules = [
            'requestee_mobile_number' => [
                'required',
                'numeric',
                'regex:/[+][9][6][4](0)?[0-9]{9,10}$/',
                'exists:users,mobile_no'
            ],
            'amount' => [
                'required',
                'integer',
                'min:250'
            ],
            'pin' => [
                'required',
                //'digits:4',
                new VerifyPIN($user)
            ],
        ];
        if(config('internal_services.fastpay_api_old')){
            unset($rules['requestee_mobile_number'][array_search('exists:users,mobile_no',$rules['requestee_mobile_number'])]);
        }
//        if(!config('internal_services.fastpay_api_old')){
//            $rules['pin'][]=(new VerifyPIN($user));
//        }
        return  $rules;
    }
}
