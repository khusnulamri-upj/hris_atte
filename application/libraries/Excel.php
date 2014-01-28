<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once APPPATH . "/third_party/PHPExcel_Classes/PHPExcel.php";

class Excel extends PHPExcel {

    public function __construct() {
        parent::__construct();
    }

}