<?php

namespace App\Http\Requests\API\v1\Users;

use App\Http\Requests\API\v1\BaseRequest;

class GetQuestionByEmailRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' =>  'required|email|exists:users',
        ];
    }
}
