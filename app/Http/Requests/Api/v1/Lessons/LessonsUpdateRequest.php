<?php

namespace App\Http\Requests\API\v1\Lessons;

use App\Http\Requests\API\v1\BaseRequest;

class LessonsUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'allow_comments' => 'required|boolean',
            'name' =>           'required|min:3|max:255',
            'description' =>    'nullable|string'
        ];
    }
}
