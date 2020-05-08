<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class YoutubeLink implements Rule
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
        // https://www.youtube.com/watch?v=0pe6AgrZFBg, https://youtu.be/0pe6AgrZFBg or https://youtu.be/0pe6AgrZFBg?t=32
        $isValidShortForm = substr($value, 0, 17) === 'https://youtu.be/' && ctype_alnum(substr($value, 17, 11)) && (strlen($value) === 28 || substr($value, 28, 3) === '?t=' && is_numeric(substr($value, 31, strlen($value) - 31)));
        $isValidLongForm = substr($value, 0, 32) === 'https://www.youtube.com/watch?v=' && ctype_alnum(substr($value, 32, 11)) && strlen($value) === 43;

        return $isValidShortForm || $isValidLongForm;
}

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Youtube link is not valid. It must be in the form: https://www.youtube.com/watch?v=0pe6AgrZFBg, https://youtu.be/0pe6AgrZFBg or https://youtu.be/0pe6AgrZFBg?t=32';
    }
}
