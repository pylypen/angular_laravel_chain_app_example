<?php

namespace App\Http\Requests\API\v1\People;

use App\Http\Requests\API\v1\BaseRequest;

class PeopleToggleAdminRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_admin' => 'required|boolean',
        ];
    }
}
