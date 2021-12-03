<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class YoutubeChannel implements Rule
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
        $channelID = str_replace('https://www.youtube.com/c/', '', trim($value, '/'));

        return preg_match('/^[^.?=&]+$/', $channelID) &&
            count($linkSegments) === 5 &&
            substr($value, 0, 26) === 'https://www.youtube.com/c/';
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The YouTube channel link is not valid. It must be in the form: https://www.youtube.com/c/{channel_id}';
    }
}
