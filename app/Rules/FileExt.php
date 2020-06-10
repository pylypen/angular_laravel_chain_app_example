<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\MediaExtensions;

class FileExt implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $mediaExtensions = [];
        foreach (MediaExtensions::get() as $me) {
            @$mediaExtensions[] = $me->media_mime;
        }

        return in_array($value->getClientMimeType(), $mediaExtensions);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.FileExt');
    }
}
