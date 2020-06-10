<?php

namespace App\Http\Requests\API\v1\Teams;

use App\Http\Requests\API\v1\BaseRequest;

class TeamRenewSettingsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'belongs_to' => 'nullable|sometimes|exists:sites,id',
        ];
    }
}
