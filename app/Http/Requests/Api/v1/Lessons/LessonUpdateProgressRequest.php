<?php

namespace App\Http\Requests\API\v1\Lessons;

use App\Http\Requests\API\v1\BaseRequest;

class LessonUpdateProgressRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'progress_status_id' =>  'required|exists:lessons_progress_status,id'
        ];
    }
}
