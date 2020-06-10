<?php

namespace App\Models;

use App\Models\Mappers\LessonComments as BaseLessonComments;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\Builder;

class LessonComments extends BaseLessonComments
{
    use Trunketable;

    /**
     * @var array
     */
    public $with = [
        'user'
    ];

    protected static function boot()
    {
        parent::boot();

        // apply automatic order_by on id field
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('id', 'desc');
        });
    }
}
