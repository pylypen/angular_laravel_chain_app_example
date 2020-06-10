<?php

namespace App\Http\Requests\API\v1\Courses;

use App\Http\Requests\API\v1\BaseRequest;

class CoursesCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|min:3'
        ];
    }
}
