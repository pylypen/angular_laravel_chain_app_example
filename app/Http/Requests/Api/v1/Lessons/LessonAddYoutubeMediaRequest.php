<?php

namespace App\Http\Requests\API\v1\Lessons;

use App\Http\Requests\API\v1\BaseRequest;
use App\Rules\YoutubeUrlValidation;

class LessonAddYoutubeMediaRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'src' => ['required', 'url', 'string', new YoutubeUrlValidation],
        ];
    }
}
