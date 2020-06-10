<?php

namespace App\Http\Requests\API\v1\Settings;

use App\Http\Requests\API\v1\BaseRequest;

class DeleteDeveloperAccessRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'acc_key' => 'required|string',
        ];
    }

}