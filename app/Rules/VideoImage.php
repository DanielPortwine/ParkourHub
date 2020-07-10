<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class VideoImage implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        $mime = $value->guessExtension();
        $size = $value->getSize();
        if (in_array($mime, ['mp4','mov','mpg','mpeg'])) {
            return $size <= 500000000;
        } else if (in_array($mime, ['jpg','jpeg','png'])) {
            return $size <= 5000000;
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
        return 'Videos must be less than 500MB and images must be less than 5MB.';
    }
}
