<?php

namespace App\Http\Requests\API\v1\Certificates;

use App\Http\Requests\API\v1\BaseRequest;
use App\Rules\CourseCompletion;

class CertificatesCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'sometimes|nullable|exists:users,id',
            'course_id' => ['required', new CourseCompletion($this->user_id)]
        ];
    }
}
