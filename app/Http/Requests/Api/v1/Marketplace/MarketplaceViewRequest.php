<?php

namespace App\Http\Requests\API\v1\Marketplace;

use App\Http\Requests\API\v1\BaseRequest;

class MarketplaceViewRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'course_id' => 'required|exists:courses,id',
            'show' => 'required|boolean',
        ];
    }
}
