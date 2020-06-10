<?php

namespace App\Models;

use App\Models\Mappers\MediaTypes as BaseMediaTypes;
use App\Models\Traits\Trunketable;

class MediaTypes extends BaseMediaTypes
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at',
    ];
}