<?php

namespace App\Models;

use App\Models\Mappers\Files as BaseFiles;
use App\Models\Traits\Trunketable;

class Files extends BaseFiles
{
    use Trunketable;

    public $hidden = [
        'user_id',
        'created_at',
        'updated_at'
    ];

}