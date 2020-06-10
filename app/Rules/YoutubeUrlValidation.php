<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class YoutubeUrlValidation implements Rule
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
        if (substr_count($value, 'youtube.com/watch?v=')) {
            return true;
        } else if (substr_count($value, 'youtu.be/')) {
            return true;
        } else if (substr_count($value, 'youtube.com/embed/')) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.YoutubeUrlValidation');
    }
}
