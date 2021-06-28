<?php
namespace App;

class Helper
{
    public static function get($array, $key, $default = '')
    {
        if ( !is_array($array)) return $default;
        return isset($array[$key]) ? $array[$key] : $default;
    }
}