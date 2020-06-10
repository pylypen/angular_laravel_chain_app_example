<?php

namespace App\Http\Requests\API\v1\Settings;

use App\Http\Requests\API\v1\BaseRequest;

class ChangePasswordUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|string',
            'new_password' => 'required|string|min:6|max:255|confirmed',
        ];
    }

}