<?php

namespace App\Http\Requests\API\v1\Users;

use App\Http\Requests\API\v1\BaseRequest;

class ConfirmEmailRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'confirm_code' => 'required|string|max:255|exists:users',
            'first_name' => 'required|string|min:2|max:255',
            'last_name' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:6|max:255|confirmed'
        ];
    }
}
