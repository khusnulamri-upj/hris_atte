<?php

class Parameter extends CI_Model {

    //var $date    = '';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function get_value($name, $type = 'VARIABLE') {
        $db_dflt = $this->load->database('default',TRUE);
        
        $id = strtoupper($name);
        $sql = "SELECT value
            FROM parameter
            WHERE type = '$type'
                AND name = '$id'
            LIMIT 1";
        $query = $db_dflt->query($sql);
        $row = $query->row();
        if (isset($row)) {
            return $row->value;
        } else {
            return NULL;
        }
        
        $db_dflt->close();
    }

}

?>
