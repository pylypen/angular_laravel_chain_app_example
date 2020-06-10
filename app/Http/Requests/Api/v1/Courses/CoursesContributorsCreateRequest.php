<?php

namespace App\Http\Requests\API\v1\Courses;

use App\Http\Requests\API\v1\BaseRequest;

class CoursesContributorsCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_id' =>  'required|exists:courses,id',
            'user_id' =>    'required|exists:users,id'
        ];
    }
}
