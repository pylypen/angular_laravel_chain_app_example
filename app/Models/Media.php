<?php

namespace App\Models;

use App\Models\Mappers\Media as BaseMedia;
use App\Models\Traits\Trunketable;

class Media extends BaseMedia
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at',
        'media_extension_id',
    ];
    protected $with = [
        'file',
        'media_extension'
    ];

}