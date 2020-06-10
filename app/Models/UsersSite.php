<?php

namespace App\Models;

use App\Models\Mappers\UsersSite as BaseUsersSite;
use App\Models\Traits\Trunketable;

class UsersSite extends BaseUsersSite
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at'
    ];
    
}