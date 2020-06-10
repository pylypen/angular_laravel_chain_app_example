<?php

namespace App\Http\Requests\API\v1\Lessons;

use App\Http\Requests\API\v1\BaseRequest;

class LessonCommentUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' =>         'required|exists:lesson_comments,id',
            'lesson_id' =>  'required|exists:lessons,id',
            'comment' =>    'required'
        ];
    }
}
