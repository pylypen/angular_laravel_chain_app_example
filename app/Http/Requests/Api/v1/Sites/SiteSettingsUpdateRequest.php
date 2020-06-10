<?php

namespace App\Http\Requests\API\v1\Sites;

use App\Http\Requests\API\v1\BaseRequest;

class SiteSettingsUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_name' => 'required|min:3|max:255',
            'admins' => 'nullable|array',
            'admins.*' => 'email'
        ];
    }
}
