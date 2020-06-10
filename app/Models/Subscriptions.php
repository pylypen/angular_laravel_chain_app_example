<?php

namespace App\Models;

use App\Models\Mappers\Subscriptions as BaseSubscriptions;
use App\Models\Traits\Trunketable;

class Subscriptions extends BaseSubscriptions
{
    use Trunketable;

    public $hidden = [
        'created_at'
    ];
}