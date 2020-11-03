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
        // https://www.youtube.com/watch?v=0pe6AgrZFBg or https://www.youtube.com/watch?v=0pe6AgrZFBg&t=32, https://youtu.be/0pe6AgrZFBg or https://youtu.be/0pe6AgrZFBg?t=32

        $youtubeID = explode('t=', str_replace([
            'https://youtu.be/',
            'https://www.youtube.com/watch?v=',
            '&',
            '?',
        ], '', $value))[0];

        return preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtubeID);
}

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The YouTube link is not valid. It must be in the form: https://www.youtube.com/watch?v=0pe6AgrZFBg, https://youtu.be/0pe6AgrZFBg.';
    }
}
