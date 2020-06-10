<?php

namespace App\Models;

use App\Models\Mappers\LessonContentOrder as BaseLessonContentOrder;

class LessonContentOrder extends BaseLessonContentOrder
{
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'id',
        'lesson_id',
        'media_type_id',
        'order'
    ];
}
