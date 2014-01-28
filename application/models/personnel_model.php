<?php

class Personnel_model extends CI_Model {

    var $tbl = 'userinfo';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function get_all_personnel_name($arr_initial = NULL) {
        $this->load->database('default');
        $col_user_id = 'user_id';
        $col_name = 'name';
        $sql = "SELECT $col_user_id,
            $col_name
            FROM $this->tbl 
            ORDER BY $col_name";
        $query = $this->db->query($sql);
        if ($arr_initial == NULL) {
            $arr_personnel_name = array();
        } else {
            $arr_personnel_name = $arr_initial;
        }
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_personnel_name[$obj->$col_user_id] = $obj->$col_name;
            }
        }
        $this->db->close();
        return $arr_personnel_name;
    }
    
    function get_all_personnel_name_by_dept_id($dept_id) {
        $this->load->database('default');
        $col_user_id = 'user_id';
        $col_name = 'name';
        $col_dept_id = 'default_dept_id';
        $sql = "SELECT $col_user_id,
            $col_name,
            $col_dept_id
            FROM $this->tbl
            WHERE $col_dept_id = $dept_id
            ORDER BY $col_name";
        $query = $this->db->query($sql);
        $arr_personnel_name = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_personnel_name[$obj->$col_user_id] = $obj->$col_name;
            }
        }
        $this->db->close();
        return $arr_personnel_name;
    }
    
    function get_personnel_name($user_id) {
        $this->load->database('default');
        $col_user_id = 'user_id';
        $col_name = 'name';
        $sql = "SELECT $col_user_id,
            $col_name
            FROM $this->tbl 
            WHERE $col_user_id = $user_id
            LIMIT 1";
        $query = $this->db->query($sql);
        $personnel_name = NULL;
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $personnel_name = $row->$col_name;
        }
        $this->db->close();
        return $personnel_name;
    }
    
    function get_dept_id($user_id) {
        $this->load->database('default');
        $col_user_id = 'user_id';
        $col_dept_id = 'default_dept_id';
        $sql = "SELECT $col_user_id,
            $col_dept_id
            FROM $this->tbl 
            WHERE $col_user_id = $user_id
            LIMIT 1";
        $query = $this->db->query($sql);
        $dept_id = NULL;
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $dept_id = $row->$col_dept_id;
        }
        $this->db->close();
        return $dept_id;
    }
}

?>
