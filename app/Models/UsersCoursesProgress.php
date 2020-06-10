<?php

namespace App\Models;

use App\Models\Mappers\UsersCoursesProgress as BaseUsersCoursesProgress;
use App\Models\Traits\Trunketable;

class UsersCoursesProgress extends BaseUsersCoursesProgress
{
    use Trunketable;

    public $hidden = [
        'created_at',
        'updated_at',
        'progress_status_id'
    ];

    protected $with = [
        'progress_status'
    ];
}