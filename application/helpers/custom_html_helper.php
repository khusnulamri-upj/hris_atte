<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('create_select')) {

    function create_select($name = NULL,$array = array(),$selected = NULL) {
        if (empty($name)) {
            $select = "<select>";
        } else {
            $select = "<select name=\"$name\">";
        }
        foreach ($array as $key => $value) {
            if ((isset($selected)) && ($selected == $key)) {
                $mark_selected = " selected=\"selected\"";
            } else {
                $mark_selected = "";
            }
            $select .= "<option value=\"$key\"$mark_selected>$value</option>";
        }
        $select .= "</select>";
        return $select;
    }

}