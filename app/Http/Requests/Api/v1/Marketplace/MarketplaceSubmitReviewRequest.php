<?php

namespace App\Http\Requests\API\v1\Marketplace;

use App\Http\Requests\API\v1\BaseRequest;

class MarketplaceSubmitReviewRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'marketplace' => 'required|array',
            'marketplace.*.id' => 'required|exists:marketplace,id',
            'marketplace.*.marketplace_status_id' => 'required|exists:marketplace_statuses,id',
            'marketplace.*.review_message' => 'sometimes|nullable'
        ];
    }
}