<?php

namespace App\Http\Requests\API\v1\Courses;

use App\Http\Requests\API\v1\BaseRequest;

class CoursesUpdateLessonsOrderRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order' => 'required|array',
            'order.*.lesson_id' => 'required|exists:lessons,id',
            'order.*.new_order' => 'required'
        ];
    }
}
