<?php

namespace App\Http\Requests\API\v1\LessonContentOrder;

use App\Http\Requests\API\v1\BaseRequest;

class LessonContentOrderRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lesson_id' => 'required|exists:lessons,id',
            'uses' => 'required'
        ];
    }
}
