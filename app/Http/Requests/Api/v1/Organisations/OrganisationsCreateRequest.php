<?php

namespace App\Http\Requests\API\v1\Organisations;

use App\Http\Requests\API\v1\BaseRequest;
use Illuminate\Validation\Rule;

class OrganisationsCreateRequest extends BaseRequest
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
            'first_name' => 'required|string|min:3|max:255',
            'last_name' =>  'required|string|min:3|max:255',
            'org_name' =>       'required|min:3|max:255'
        ];
    }
}
