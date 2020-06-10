<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseCompletion implements Rule
{
    protected $user_id;

    public function __construct($user_id = false)
    {
        $this->user_id = $user_id;
    }
    
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user_id = empty((int)$this->user_id) ? (int)$value : (int)$this->user_id;
        
        $course = Course::select([
            'courses.*',
            DB::raw('ROUND(AVG(lessons_progress_status.percent)) as completion')
        ])
            ->leftJoin('lessons', function ($join) {
                $join->on('lessons.course_id', '=', 'courses.id');
                $join->where('lessons.deleted_at', null);
            })
            ->leftJoin('users_courses_progress', function ($join) use ($user_id) {
                $join->on('users_courses_progress.lesson_id', '=', 'lessons.id');
                $join->where('users_courses_progress.user_id', $user_id);
            })
            ->leftJoin('lessons_progress_status', 'lessons_progress_status.id', '=', 'users_courses_progress.progress_status_id')
            ->where('courses.id', $value)
            ->groupBy('courses.id')
            ->first();

        return ($course && $course->completion == 100);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.CourseCompletion');
    }
}
