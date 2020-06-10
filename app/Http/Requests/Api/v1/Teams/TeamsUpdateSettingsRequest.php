<?php

namespace App\Http\Requests\API\v1\Teams;

use App\Http\Requests\API\v1\BaseRequest;

class TeamsUpdateSettingsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'team_name'     => 'required|min:3|max:255',
            'admins'        => 'nullable|array',
            'admins.*'      => 'email',
            'belongs_to'    => 'nullable|exists:sites,id',
        ];
    }
}
