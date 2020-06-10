<?php

namespace App\Http\Requests\API\v1\Teams;

use App\Http\Requests\API\v1\BaseRequest;

class TeamUpdateMembersRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'members'        => 'nullable|array',
            'members.*'      => 'email'
        ];
    }
}
