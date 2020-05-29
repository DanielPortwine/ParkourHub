<?php

if (!function_exists('quantify_number')) {
    function quantify_number($number)
    {
        switch (strlen((string)$number)) {
            case 4:
            case 5:
            case 6:
                $number = round($number / 1000, 1) . 'k';
                break;
            case 7:
            case 8:
            case 9:
                $number = round($number / 1000000, 1) . 'm';
                break;
            case 10:
            case 11:
            case 12:
                $number = round($number / 1000000000, 1) . 'b';
                break;
            default:
                break;
        }

        return $number;
    }
}
