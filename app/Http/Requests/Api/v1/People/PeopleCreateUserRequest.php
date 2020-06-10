<?php

namespace App\Http\Requests\API\v1\People;

use App\Http\Requests\API\v1\BaseRequest;
use App\Rules\BirthdayRange;
use Illuminate\Validation\Rule;

class PeopleCreateUserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'   => ['required', 'string', 'email', 'max:255', Rule::unique('users')->where(function ($query) {
                return $query->where(['deleted_at' => NULL]);
            })],
            'contact_email' => 'nullable|email',
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