<?php

namespace App\Models;

use App\Models\Mappers\MediaExtensions as BaseMediaExtensions;
use App\Models\Traits\Trunketable;

class MediaExtensions extends BaseMediaExtensions
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at',
        'media_type_id'
    ];

    protected $with = [
        'media_type'
    ];
}