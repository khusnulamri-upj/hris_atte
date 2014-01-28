<?php

class Import_model extends CI_Model {
    
    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
    
    function set_mdb_connect() {
        include('/assets/location.php');
        echo $mdb_remote_location_for_model;
        
        $file_path = $mdb_host_location.$mdb_remote_location_for_model.$mdb_remote_filename;
        //$file_path = $this->Parameter->get_value('FILE_ON_SERVER_FOR_MDB');
        
        $config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=".$file_path;
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=".$file_path;
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";
        
        return $config;
    }
    
    function get_checkinout_mdb() {
        $config = $this->set_mdb_connect();
        $db_mdb = $this->load->database($config, TRUE);
        
        $sql_mdb = "SELECT USERID AS user_id,
            CHECKTIME AS check_time,
            CHECKTYPE AS check_type,
            VERIFYCODE AS verify_code,
            SENSORID AS sensor_id, 
            WORKCODE AS work_code,
            sn 
            FROM CHECKINOUT";
        
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        $result = $qry_mdb->result();
        
        $db_mdb->close();
        
        return $result;
    }
    
    function insert_into_checkinout_temp($result_mdb) {
        $table = 'mdb_checkinout';
        
        $db_mysql = $this->load->database('temporary', TRUE);
        
        $db_mysql->trans_start();
        
        $db_mysql->truncate($table);
        
        $i = 0;
        
        foreach ($result_mdb as $row_mdb) {
            $data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'check_time' => $row_mdb->check_time,
                'check_type' => $row_mdb->check_type,
                'verify_code' => $row_mdb->verify_code,
                'sensor_id' => $row_mdb->sensor_id,
                'work_code' => $row_mdb->work_code,
                'sn' => $row_mdb->sn
            );
            $db_mysql->insert($table, $data_mysql);
            $i++;
        }
        
        $db_mysql->trans_complete();
        
        //AMRNOTE: NEED TO BACKUP
        //bila data attendance PRIMARY (tanggal sebelum max_date) hilang tidak dapat di-import
        
        $db_mysql->close();
        
        return $i;
    }
    
    //ATTENDANCE PROCESS --START
    //cek apa data sudah ada di TBL attendance
    //dengan mengecek COL user_id, date
    //1. bandingkan DB temp TAB mdb_checkinout COL user_id dengan DB presensi TAB attendance COL user_id
    //--->> bila semua user_id sama (tidak terjadi penambahan)
    //------:: sql dengan filter max_date
    //--->> bila ada user_id beda (terjadi penambahan)
    //------:: cari user_id yang beda,, sql untuk semua (tanpa filter max_date)
    //20131204
    //2. tgl yg akan di-insert harus mulai dari tanggal max,
    //   karena data bisa jadi belum lengkap pada tanggal max,
    //   maka perlu dilakukan delete pada tanggal max
    
    function get_checkinout_max_date_primary() {
        //MAX DATE PER USER ID
        $db_dflt = $this->load->database('default', TRUE);
        $sql_dflt_max_date = "SELECT user_id,
            MAX(date) AS max_date,
            IF(YEAR(MAX(date)) IS NULL,0,YEAR(MAX(date))) AS year_max_date,
            IF(DAYOFYEAR(MAX(date)) IS NULL,0,DAYOFYEAR(MAX(date))) AS day_max_date
            FROM attendance
            GROUP BY user_id";
        
        $qry_dflt_max_date = $db_dflt->query($sql_dflt_max_date);
        $arr_dflt_max_date = array();
        $index = 0;
        foreach ($qry_dflt_max_date->result() as $row_dflt_max_date) {
            $arr_dflt_max_date[$index] = new stdClass();
            $arr_dflt_max_date[$index]->user_id = $row_dflt_max_date->user_id;
            $arr_dflt_max_date[$index]->year_max_date = $row_dflt_max_date->year_max_date;
            $arr_dflt_max_date[$index]->day_max_date = $row_dflt_max_date->day_max_date;
            $index++;
        }
        $db_dflt->close();
        
        return $arr_dflt_max_date;
    }
    
    function get_checkinout_after_max_date_temp($arr_dflt_max_date) {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        $arr_dflt_user_id = array();
        
        foreach ($arr_dflt_max_date as $max_date) {
            $arr_dflt_user_id[] = $max_date->user_id;
        }
        
        $db_temp = $this->load->database('temporary', TRUE);
        
        $sql_temp_user_id = "SELECT user_id
            FROM mdb_checkinout
            GROUP BY user_id";
        
        $index = sizeof($arr_dflt_max_date);
        //FIND DIFFERENT USER ID
        $qry_temp_user_id = $db_temp->query($sql_temp_user_id);
        foreach ($qry_temp_user_id->result() as $row_temp_user_id) {
            if (!in_array($row_temp_user_id->user_id, $arr_dflt_user_id)) {
                $arr_dflt_max_date[$index] = new stdClass();
                $arr_dflt_max_date[$index]->user_id = $row_temp_user_id->user_id;
                $arr_dflt_max_date[$index]->year_max_date = '0';
                $arr_dflt_max_date[$index]->day_max_date = '0';
                $index++;
            }
        }
        
        //PROCCESSING DATA
        $arr_temp_all_import = array();
        foreach ($arr_dflt_max_date as $row_max_date) {
            if (($row_max_date->year_max_date == '0') && ($row_max_date->day_max_date == '0')) {
                $fltr_max_date = "";
            } else {
                //$fltr_max_date = " AND DATE(check_time) > MAKEDATE($row_max_date->year_max_date,$row_max_date->day_max_date)";
                //20131204
                $fltr_max_date = " AND DATE(check_time) >= MAKEDATE($row_max_date->year_max_date,$row_max_date->day_max_date)";
            }
            $sql_temp_import = "SELECT user_id,
                DATE(check_time) AS date,
                MAKETIME(HOUR(MIN(check_time)),MINUTE(MIN(check_time)),00) AS min_time,
                MAKETIME(HOUR(MAX(check_time)),MINUTE(MAX(check_time)),00) AS max_time
                FROM mdb_checkinout
                WHERE user_id = $row_max_date->user_id".$fltr_max_date
                ." GROUP BY user_id, DATE(check_time)
                ORDER BY user_id, DATE(check_time)";
            $qry_temp_import = $db_temp->query($sql_temp_import);
            foreach ($qry_temp_import->result() as $row_temp_import) {
                $arr_temp_all_import[] = array(
                    'created_by' => $current_logged_in_user,
                    'user_id' => $row_temp_import->user_id,
                    'date' => $row_temp_import->date,
                    'min_time' => $row_temp_import->min_time,
                    'max_time' => $row_temp_import->max_time
                );
            }
        }
        $db_temp->close();
        
        return $arr_temp_all_import;
    }
    
    //20131104
    function delete_checkinout_max_date_primary($arr_dflt_max_date) {
        $db_dflt = $this->load->database('default', TRUE);
        
        foreach ($arr_dflt_max_date as $row_max_date) {
            $sql_dflt_del_max_date = "DELETE FROM attendance
                WHERE date = MAKEDATE($row_max_date->year_max_date,$row_max_date->day_max_date) AND user_id = $row_max_date->user_id";
        
            $qry_dflt_del_max_date = $db_dflt->query($sql_dflt_del_max_date);
        }
        
        $db_dflt->close();
    }
    
    function insert_into_checkinout_primary($arr_temp_all_import) {
        if (sizeof($arr_temp_all_import) > 0) {
            $db_dflt = $this->load->database('default', TRUE);
            $db_dflt->insert_batch('attendance', $arr_temp_all_import);
            $db_dflt->close();
        }
        
        return sizeof($arr_temp_all_import);
    }
    
    //ATTENDANCE PROCESS --END
    
    function get_checkinout() {
        $result_checkinout = $this->get_checkinout_mdb();
        $row_checkinout = $this->insert_into_checkinout_temp($result_checkinout);
        
        return $row_checkinout;
    }
    
    function process_checkinout() {
        $arr_dflt_max_date = $this->get_checkinout_max_date_primary();
        $this->delete_checkinout_max_date_primary($arr_dflt_max_date);
        $arr_temp_all_import = $this->get_checkinout_after_max_date_temp($arr_dflt_max_date);
        $row_inserted = $this->insert_into_checkinout_primary($arr_temp_all_import);
        
        return $row_inserted;
    }
    
    function get_department_mdb() {
        $config = $this->set_mdb_connect();
        $db_mdb = $this->load->database($config, TRUE);
        
        $sql_mdb = "SELECT DEPTID AS dept_id,
            DEPTNAME AS dept_name,
            SUPDEPTID AS sup_dept_id, 
            InheritParentSch AS inherit_parent_sch,
            InheritDeptSch AS inherit_dept_sch, 
            InheritDeptSchClass AS inherit_dept_sch_class,
            AutoSchPlan AS auto_sch_plan, 
            InLate AS in_late,
            OutEarly AS out_early,
            InheritDeptRule AS inherit_dept_rule, 
            MinAutoSchInterval AS min_auto_sch_interval,
            RegisterOT AS register_ot, 
            DefaultSchId AS default_sch_id,
            att,
            holiday,
            OverTime AS over_time 
            FROM DEPARTMENTS";
        
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        $result = $qry_mdb->result();
        
        $db_mdb->close();
        
        return $result;
    }
    
    function insert_into_department_temp($result_mdb) {
        $table = 'mdb_departments';
        
        $db_mysql = $this->load->database('temporary', TRUE);
        
        $db_mysql->trans_start();
        
        $db_mysql->truncate($table);
        
        $i = 0;
        
        foreach ($result_mdb as $row_mdb) {
            $data_mysql = array(
                'dept_id' => $row_mdb->dept_id,
                'dept_name' => $row_mdb->dept_name,
                'sup_dept_id' => $row_mdb->sup_dept_id,
                'inherit_parent_sch' => $row_mdb->inherit_parent_sch,
                'inherit_dept_sch' => $row_mdb->inherit_dept_sch,
                'inherit_dept_sch_class' => $row_mdb->inherit_dept_sch_class,
                'auto_sch_plan' => $row_mdb->auto_sch_plan,
                'in_late' => $row_mdb->in_late,
                'out_early' => $row_mdb->out_early,
                'inherit_dept_rule' => $row_mdb->inherit_dept_rule,
                'min_auto_sch_interval' => $row_mdb->min_auto_sch_interval,
                'register_ot' => $row_mdb->register_ot,
                'default_sch_id' => $row_mdb->default_sch_id,
                'att' => $row_mdb->att,
                'holiday' => $row_mdb->holiday,
                'over_time' => $row_mdb->over_time
            );
            $db_mysql->insert($table, $data_mysql);
            $i++;
        }
        
        $db_mysql->trans_complete();
        
        $db_mysql->close();
        
        return $i;
    }
    
    function get_userinfo_mdb() {
        $config = $this->set_mdb_connect();
        $db_mdb = $this->load->database($config, TRUE);
        
        $sql_mdb = "SELECT USERID AS user_id,
            Badgenumber AS badge_number,
            ssn,
            name,
            gender,
            title,
            pager,
            BIRTHDAY AS birth_day,
            HIREDDAY AS hired_day,
            street,
            city,
            state,
            zip,
            OPHONE AS o_phone,
            FPHONE AS f_phone,
            VERIFICATIONMETHOD AS verification_method,
            DEFAULTDEPTID AS default_dept_id,
            SECURITYFLAGS AS security_flags,
            att,
            INLATE AS in_late,
            OUTEARLY AS out_early,
            overtime,
            sep,
            holiday,
            minzu,
            password,
            LUNCHDURATION AS lunch_duration,
            MVERIFYPASS AS m_verify_pass,
            photo,
            notes,
            privilege,
            InheritDeptSch AS inherit_dept_sch,
            InheritDeptSchClass AS inherit_dept_sch_class,
            AutoSchPlan AS auto_sch_plan,
            MinAutoSchInterval AS min_auto_sch_interval,
            RegisterOT AS register_ot,
            InheritDeptRule AS inherit_dept_rule,
            emprivilege,
            CardNo AS card_no,
            pin1
            FROM USERINFO";
        
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        $result = $qry_mdb->result();
        
        $db_mdb->close();
        
        return $result;
    }
    
    function insert_into_userinfo_temp($result_mdb) {
        $table = 'mdb_userinfo';
        
        $db_mysql = $this->load->database('temporary', TRUE);
        
        $db_mysql->trans_start();
        
        $db_mysql->truncate($table);
        
        $i = 0;
        
        foreach ($result_mdb as $row_mdb) {
            $data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'badge_number' => $row_mdb->badge_number,
                'ssn' => $row_mdb->ssn,
                'name' => $row_mdb->name,
                'gender' => $row_mdb->gender,
                'title' => $row_mdb->title,
                'pager' => $row_mdb->pager,
                'birth_day' => $row_mdb->birth_day,
                'hired_day' => $row_mdb->hired_day,
                'street' => $row_mdb->street,
                'city' => $row_mdb->city,
                'state' => $row_mdb->state,
                'zip' => $row_mdb->zip,
                'o_phone' => $row_mdb->o_phone,
                'f_phone' => $row_mdb->f_phone,
                'verification_method' => $row_mdb->verification_method,
                'default_dept_id' => $row_mdb->default_dept_id,
                'security_flags' => $row_mdb->security_flags,
                'att' => $row_mdb->att,
                'in_late' => $row_mdb->in_late,
                'out_early' => $row_mdb->out_early,
                'overtime' => $row_mdb->overtime,
                'sep' => $row_mdb->sep,
                'holiday' => $row_mdb->holiday,
                'minzu' => $row_mdb->minzu,
                'password' => $row_mdb->password,
                'lunch_duration' => $row_mdb->lunch_duration,
                'm_verify_pass' => $row_mdb->m_verify_pass,
                'photo' => $row_mdb->photo,
                'notes' => $row_mdb->notes,
                'privilege' => $row_mdb->privilege,
                'inherit_dept_sch' => $row_mdb->inherit_dept_sch,
                'inherit_dept_sch_class' => $row_mdb->inherit_dept_sch_class,
                'auto_sch_plan' => $row_mdb->auto_sch_plan,
                'min_auto_sch_interval' => $row_mdb->min_auto_sch_interval,
                'register_ot' => $row_mdb->register_ot,
                'inherit_dept_rule' => $row_mdb->inherit_dept_rule,
                'emprivilege' => $row_mdb->emprivilege,
                'card_no' => $row_mdb->card_no,
                'pin1' => $row_mdb->pin1               
            );
            $db_mysql->insert($table, $data_mysql);
            $i++;
        }
        
        $db_mysql->trans_complete();
        
        $db_mysql->close();
        
        return $i;
    }
    
    function get_department_temp() {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        $arr_temp_all_import_dept = array();
        
        $db_temp = $this->load->database('temporary', TRUE);
        
        $sql_temp_import_dept = "SELECT * FROM mdb_departments";
        $qry_temp_import_dept = $db_temp->query($sql_temp_import_dept);
        foreach ($qry_temp_import_dept->result() as $row_temp_import_dept) {
            $arr_temp_all_import_dept[] = array(
                'created_by' => $current_logged_in_user,
                'dept_id' => $row_temp_import_dept->dept_id,
                'dept_name' => $row_temp_import_dept->dept_name,
                'sup_dept_id' => $row_temp_import_dept->sup_dept_id,
                'inherit_parent_sch' => $row_temp_import_dept->inherit_parent_sch,
                'inherit_dept_sch' => $row_temp_import_dept->inherit_dept_sch,
                'inherit_dept_sch_class' => $row_temp_import_dept->inherit_dept_sch_class,
                'auto_sch_plan' => $row_temp_import_dept->auto_sch_plan,
                'in_late' => $row_temp_import_dept->in_late,
                'out_early' => $row_temp_import_dept->out_early,
                'inherit_dept_rule' => $row_temp_import_dept->inherit_dept_rule,
                'min_auto_sch_interval' => $row_temp_import_dept->min_auto_sch_interval,
                'register_ot' => $row_temp_import_dept->register_ot,
                'default_sch_id' => $row_temp_import_dept->default_sch_id,
                'att' => $row_temp_import_dept->att,
                'holiday' => $row_temp_import_dept->holiday,
                'over_time' => $row_temp_import_dept->over_time
            );
        }
        
        $db_temp->close();
        
        return $arr_temp_all_import_dept;
    }
    
    function insert_into_department_primary($arr_temp_all_import) {
        $table = 'department';
        
        if (sizeof($arr_temp_all_import) > 0) {
            $db_dflt = $this->load->database('default', TRUE);
            $db_dflt->truncate($table);
            $db_dflt->insert_batch($table, $arr_temp_all_import);
            $db_dflt->close();
        }
        
        return sizeof($arr_temp_all_import);
    }
    
    function get_userinfo_temp() {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        $arr_temp_all_import_user = array();
        
        $db_temp = $this->load->database('temporary', TRUE);
        
        $sql_temp_import_user = "SELECT * FROM mdb_userinfo";
        $qry_temp_import_user = $db_temp->query($sql_temp_import_user);
        foreach ($qry_temp_import_user->result() as $row_temp_import_user) {
            $arr_temp_all_import_user[] = array(
                'created_by' => $current_logged_in_user,
                'user_id' => $row_temp_import_user->user_id,
                'badge_number' => $row_temp_import_user->badge_number,
                'ssn' => $row_temp_import_user->ssn,
                'name' => $row_temp_import_user->name,
                'gender' => $row_temp_import_user->gender,
                'title' => $row_temp_import_user->title,
                'pager' => $row_temp_import_user->pager,
                'birth_day' => $row_temp_import_user->birth_day,
                'hired_day' => $row_temp_import_user->hired_day,
                'street' => $row_temp_import_user->street,
                'city' => $row_temp_import_user->city,
                'state' => $row_temp_import_user->state,
                'zip' => $row_temp_import_user->zip,
                'o_phone' => $row_temp_import_user->o_phone,
                'f_phone' => $row_temp_import_user->f_phone,
                'verification_method' => $row_temp_import_user->verification_method,
                'default_dept_id' => $row_temp_import_user->default_dept_id,
                'security_flags' => $row_temp_import_user->security_flags,
                'att' => $row_temp_import_user->att,
                'in_late' => $row_temp_import_user->in_late,
                'out_early' => $row_temp_import_user->out_early,
                'overtime' => $row_temp_import_user->overtime,
                'sep' => $row_temp_import_user->sep,
                'holiday' => $row_temp_import_user->holiday,
                'minzu' => $row_temp_import_user->minzu,
                'password' => $row_temp_import_user->password,
                'lunch_duration' => $row_temp_import_user->lunch_duration,
                'm_verify_pass' => $row_temp_import_user->m_verify_pass,
                'photo' => $row_temp_import_user->photo,
                'notes' => $row_temp_import_user->notes,
                'privilege' => $row_temp_import_user->privilege,
                'inherit_dept_sch' => $row_temp_import_user->inherit_dept_sch,
                'inherit_dept_sch_class' => $row_temp_import_user->inherit_dept_sch_class,
                'auto_sch_plan' => $row_temp_import_user->auto_sch_plan,
                'min_auto_sch_interval' => $row_temp_import_user->min_auto_sch_interval,
                'register_ot' => $row_temp_import_user->register_ot,
                'inherit_dept_rule' => $row_temp_import_user->inherit_dept_rule,
                'emprivilege' => $row_temp_import_user->emprivilege,
                'card_no' => $row_temp_import_user->card_no,
                'pin1' => $row_temp_import_user->pin1
            );
        }
        
        $db_temp->close();
        
        return $arr_temp_all_import_user;
    }
    
    function insert_into_userinfo_primary($arr_temp_all_import) {
        $table = 'userinfo';
        
        if (sizeof($arr_temp_all_import) > 0) {
            $db_dflt = $this->load->database('default', TRUE);
            $db_dflt->truncate($table);
            $db_dflt->insert_batch($table, $arr_temp_all_import);
            $db_dflt->close();
        }
        
        return sizeof($arr_temp_all_import);
    }
    
    function get_department() {
        $result = $this->get_department_mdb();
        $row = $this->insert_into_department_temp($result);
        return $row;
    }
    
    function process_department() {
        $arr_temp = $this->get_department_temp();
        $row = $this->insert_into_department_primary($arr_temp);
        return $row;
    }
    
    function get_userinfo() {
        $result = $this->get_userinfo_mdb();
        $row = $this->insert_into_userinfo_temp($result);
        return $row;
    }
    
    function process_userinfo() {
        $arr_temp = $this->get_userinfo_temp();
        $row = $this->insert_into_userinfo_primary($arr_temp);
        return $row;
    }
}

?>
