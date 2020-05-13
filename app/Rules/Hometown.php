<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Hometown implements Rule
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
        $values = explode('|', $value);
        if (count($values) === 2) {
            $elementOneIsString = is_string($values[0]);
            $elementTwo = explode(',', $values[1]);
            $elementTwoIsBounding = count($elementTwo) === 4;
            if ($elementTwoIsBounding) {
                foreach ($elementTwo as $key => $side) {
                    if (!is_numeric($side) && !((($key === 0 || $key === 1) && (-90 < $side && $side < 90)) || (($key === 2 || $key === 3) && (-180 < $side && $side < 180)))) {
                        $elementTwoIsBounding = false;
                        break;
                    }
                }
            }

            return $elementOneIsString && $elementTwoIsBounding;
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Hometown is not valid.';
    }
}
