<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class NotAutoUsername implements Rule
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
        $startsUser = substr(strtolower($value), 0, 4) === 'user';
        $removedUser = str_replace('user', '', strtolower($value));
        $isInt = is_int($removedUser);
        $isUserID = Auth::check() ? (int)$removedUser === Auth::id() : false;

        return !$startsUser || ($isInt && !$isUserID) || ($startsUser && $isUserID);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The username must not be in the format: Userx';
    }
}
