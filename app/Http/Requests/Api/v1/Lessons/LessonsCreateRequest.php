<?php

namespace App\Http\Requests\API\v1\Lessons;

use App\Http\Requests\API\v1\BaseRequest;

class LessonsCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_id' =>      'required|exists:courses,id',
            'name' =>           'required|min:3|max:255'
        ];
    }
}
