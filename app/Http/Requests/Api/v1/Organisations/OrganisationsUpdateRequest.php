<?php

namespace App\Http\Requests\API\v1\Organisations;

use App\Http\Requests\API\v1\BaseRequest;

class OrganisationsUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'legal_name' =>     'required|min:3|max:255',
            'display_name' =>   'required|min:3|max:255',
            'mission' =>        'required|min:3|max:255',
            'logo' =>           'string|max:255',
            'lat' =>            'numeric',
            'lng' =>            'numeric'
        ];
    }
}
