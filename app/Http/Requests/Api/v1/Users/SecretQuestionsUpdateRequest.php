<?php

namespace App\Http\Requests\API\v1\Users;

use App\Http\Requests\API\v1\BaseRequest;

class SecretQuestionsUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'secret_questions_id' =>    'required|exists:secret_questions,id',
            'secret_answer' =>          'required|min:3|max:255'
        ];
    }
}
