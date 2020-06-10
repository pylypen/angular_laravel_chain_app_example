<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $course_id
 * @property int $order
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Course $course
 * @property Media[] $media
 */
class Lesson extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lessons';

    /**
     * @var array
     */
    protected $fillable = ['course_id', 'order', 'name', 'description', 'allow_comments'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo('App\Models\Course');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lesson_content_order()
    {
        return $this->hasMany('App\Models\LessonContentOrder');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function progress()
    {
        return $this->hasOne('App\Models\UsersCoursesProgress');
    }
}
