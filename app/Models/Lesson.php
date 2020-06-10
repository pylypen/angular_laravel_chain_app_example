<?php

namespace App\Models;

use App\Models\Mappers\Lesson as BaseLesson;
use App\Models\Traits\Trunketable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Lesson extends BaseLesson
{
	use Trunketable, SoftDeletes;

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent::boot();

        // apply automatic order_by on lesson order field
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

}
