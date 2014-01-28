<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('get_all_month_name')) {

    function get_all_month_name($language = 'indonesia') {
        $arr_month = array();
        if (strtoupper($language) == 'INDONESIA') {
            $arr_month = array(1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember');
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $arr_month[$i] = date("F", mktime(0, 0, 0, 1, $i, date("Y")));
            }
        }
        return $arr_month;
    }

}

if (!function_exists('get_month_name')) {

    function get_month_name($month_id = 1, $language = 'indonesia') {
        $arr_month = array();
        if (strtoupper($language) == 'INDONESIA') {
            $arr_month = array(1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember');
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $arr_month[$i] = date("F", mktime(0, 0, 0, 1, $i, date("Y")));
            }
        }
        return $arr_month[intval($month_id)];
    }

}