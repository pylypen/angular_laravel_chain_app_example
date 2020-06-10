<?php

namespace App\Http\Requests\API\v1\Settings;

use App\Http\Requests\API\v1\BaseRequest;

class AccountSettingsUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'name' => 'required|string|max:255|min:3',
            'phone_number' => 'nullable|phone:US',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'street' => 'nullable|string',
            'zip' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'phone_number.phone' => 'The :attribute must be valid US phone number.'
        ];
    }

}