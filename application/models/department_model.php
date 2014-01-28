<?php

class Department_model extends CI_Model {

    var $tbl = 'department';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function get_all_department_name($arr_initial = NULL) {
        $this->load->database('default');
        $col_dept_id = 'dept_id';
        $col_dept_name = 'dept_name';
        $sql = "SELECT $col_dept_id,
            $col_dept_name
            FROM $this->tbl 
            ORDER BY $col_dept_name";
        $query = $this->db->query($sql);
        if ($arr_initial == NULL) {
            $arr_dept_name = array();
        } else {
            $arr_dept_name = $arr_initial;
        }
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_dept_name[$obj->$col_dept_id] = $obj->$col_dept_name;
            }
        }
        $this->db->close();
        return $arr_dept_name;
    }
    
    function get_department_name($dept_id) {
        $this->load->database('default');
        $col_dept_id = 'dept_id';
        $col_dept_name = 'dept_name';
        $sql = "SELECT $col_dept_id,
            $col_dept_name
            FROM $this->tbl 
            WHERE $col_dept_id = $dept_id
            LIMIT 1";
        $query = $this->db->query($sql);
        $department_name = NULL;
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $department_name = $row->$col_dept_name;
        }
        $this->db->close();
        return $department_name;
    }
    
    function get_all_department_id() {
        $this->load->database('default');
        $col_dept_id = 'dept_id';
        $col_dept_name = 'dept_name';
        $sql = "SELECT $col_dept_id,
            $col_dept_name
            FROM $this->tbl 
            ORDER BY $col_dept_name";
        $query = $this->db->query($sql);
        $arr_dept_name = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_dept_name[$obj->$col_dept_id] = $obj->$col_dept_id;
            }
        }
        $this->db->close();
        return $arr_dept_name;
    }
}

?>
