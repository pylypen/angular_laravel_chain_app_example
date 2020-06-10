<?php

namespace App\Http\Requests\API\v1\Users;

use App\Http\Requests\API\v1\BaseRequest;

class UserUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string|min:3|max:255',
            'last_name' =>  'required|string|min:3|max:255',
            'email' =>      'required|string|email|max:255|unique:users',
            'password' =>   'required|string|min:6|max:255|confirmed'
        ];
    }
}
