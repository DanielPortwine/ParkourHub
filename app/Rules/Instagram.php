<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Instagram implements Rule
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
        $linkSegments = explode('/', trim($value, '/'));
        $handle = str_replace('https://www.instagram.com/', '', trim($value, '/'));

        return preg_match('/^[^.?=&]+$/', $handle) &&
            count($linkSegments) === 4 &&
            substr($value, 0, 26) === 'https://www.instagram.com/';
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Instagram link is not valid. It must be in the form: https://www.instagram.com/{ig_handle}';
    }
}
