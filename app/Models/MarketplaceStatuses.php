<?php

namespace App\Models;

use App\Models\Mappers\MarketplaceStatuses as BaseMarketplaceStatuses;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceStatuses extends BaseMarketplaceStatuses
{
    use Trunketable, SoftDeletes;

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}