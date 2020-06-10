<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BirthdayRange implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    
    private $minYear = 16;
    private $maxYear = 100;
    
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $minmktime  = mktime(0, 0, 0, date("m"),   date("d"),   date("Y") - $this->minYear);
        $maxmktime  = mktime(0, 0, 0, date("m"),   date("d"),   date("Y") - $this->maxYear);
        
        return $minmktime >= strtotime($value) && $maxmktime <= strtotime($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.BirthdayRange', ['min' => $this->minYear, 'max' => $this->maxYear]);
    }
}
