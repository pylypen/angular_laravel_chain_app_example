<?php

namespace App\Http\Requests\API\v1\Marketplace;

use App\Http\Requests\API\v1\BaseRequest;

class MarketplacePersonalAssignRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'marketplace_id' => 'required|exists:marketplace,id',
            'users' => 'present|array',
            'users.*' => 'sometimes|required|exists:users,id'
        ];
    }
}