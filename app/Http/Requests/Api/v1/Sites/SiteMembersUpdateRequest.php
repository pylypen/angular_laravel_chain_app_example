<?php

namespace App\Http\Requests\API\v1\Sites;

use App\Http\Requests\API\v1\BaseRequest;

class SiteMembersUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'members' => 'required|array',
            'members.*' => 'email'
        ];
    }
}
