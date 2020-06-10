<?php

namespace App\Models;

use App\Models\Mappers\LessonsProgressStatus as BaseLessonsProgressStatus;
use App\Models\Traits\Trunketable;

class LessonsProgressStatus extends BaseLessonsProgressStatus
{
    use Trunketable;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}