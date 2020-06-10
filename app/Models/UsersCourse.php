<?php

namespace App\Models;

use App\Models\Mappers\UsersCourse as BaseUsersCourse;
use App\Models\Traits\Trunketable;

class UsersCourse extends BaseUsersCourse
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at'
    ];

}