<?php

namespace App\Http\Requests\API\v1\Certificates;

use App\Http\Requests\API\v1\BaseRequest;
use App\Rules\CourseCompletion;

class CertificatesCreateEmailRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_id' => ['required', new CourseCompletion],
            'email' => ['required', 'email']
        ];
    }
}
