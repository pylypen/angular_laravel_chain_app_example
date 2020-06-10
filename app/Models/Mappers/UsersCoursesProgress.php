<?php

namespace App\Models\Mappers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $course_id
 * @property int $lesson_id
 * @property int $progress_status_id
 * @property string $created_at
 * @property string $updated_at
 * @property UsersCourse $usersCourses
 * @property Lesson $lesson
 * @property LessonsProgressStatus $progress_status
 */
class UsersCoursesProgress extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_courses_progress';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'progress_status_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson', 'lesson_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function progress_status()
    {
        return $this->belongsTo('App\Models\LessonsProgressStatus', 'progress_status_id');
    }
}
