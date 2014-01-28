<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('get_array_value_do_ucwords')) {

    function get_array_value_do_ucwords($array = array()) {
        foreach ($array as $key => $value) {
            $array[$key] = ucwords(strtolower($value));
        }
        return $array;
    }

}

if (!function_exists('do_ucwords')) {

    function do_ucwords($string = array()) {
        $ucstring = ucwords(strtolower($string));
        return $ucstring;
    }

}