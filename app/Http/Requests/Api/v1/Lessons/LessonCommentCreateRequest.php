<?php

namespace App\Http\Requests\API\v1\Lessons;

use App\Http\Requests\API\v1\BaseRequest;

class LessonCommentCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lesson_id' =>  'required|exists:lessons,id',
            'comment' =>    'required'
        ];
    }
}
