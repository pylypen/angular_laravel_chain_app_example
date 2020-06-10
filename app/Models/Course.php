<?php

namespace App\Models;

use App\Models\Mappers\Course as BaseCourse;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends BaseCourse
{
    use Trunketable, SoftDeletes;

    public $hidden = [
        'original_author_id',
        'thumbnail_id',
        'featured_background_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public $with = [
        'featured_background',
        'thumbnail'
    ];
}