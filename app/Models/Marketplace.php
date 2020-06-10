<?php

namespace App\Models;

use App\Models\Mappers\Marketplace as BaseMarketplace;
use App\Models\Traits\Trunketable;

class Marketplace extends BaseMarketplace
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at',
        'marketplace_status_id',
    ];

    protected $with = [
        'status'
    ];
}