<?php



namespace App\Helpers;

use Morilog\Jalali\Jalalian;

class JalaliHelper
{
    public static function convertToJalali($date)
    {
        return Jalalian::fromDateTime($date)->format('Y/m/d H:i:s');
    }
    public static function convertToJalali2($date)
    {
        return Jalalian::fromDateTime($date)->format('Y/m/d');
    }
}