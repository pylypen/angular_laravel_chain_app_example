<?php

namespace App\Http\Requests\API\v1\People;

use App\Http\Requests\API\v1\BaseRequest;
use App\Rules\BirthdayRange;

class PeopleUpdateUserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contact_email' => 'required|email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'birthday' => ['nullable', 'date', new BirthdayRange],
            'phone_number' => 'nullable|phone:US'
        ];
    }

    public function messages()
    {
        return [
            'phone_number.phone' => 'The :attribute must be valid US phone number.'
        ];
    }

}