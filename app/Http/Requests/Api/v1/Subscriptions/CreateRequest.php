<?php

namespace App\Http\Requests\API\v1\Subscriptions;

use App\Http\Requests\API\v1\BaseRequest;

class CreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plan' =>  'required',
            'number' =>  'required|digits_between:14,16',
            'exp_month' =>  'required|numeric|digits_between:1,12',
            'exp_year' =>  'required|numeric|between:2019,2030',
            'cvc' =>  'required|between:000,999|numeric',
            'name' =>  'required',
        ];
    }
}
