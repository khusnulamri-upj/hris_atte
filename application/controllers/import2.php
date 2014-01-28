<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Import extends CI_Controller {
    
    var $mdb_config = array();
    
    public function index() {
        /*$config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)};DBQ=D:\UPJ\Attendance\att2000.mdb";
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)};DBQ=D:\UPJ\Attendance\att2000.mdb";
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $this->load->database($config);

        $query = $this->db->query("SELECT * FROM CHECKINOUT");

        foreach ($query->result() as $row) {
            echo $row->USERID;
            //echo $row->body;
        }

        $this->db->close();*/

        //$this->load->view('welcome_message');
        //$data['mdbfilepath_local'] = $this->Parameter->get_value('mdb_local_file_path');
        //$data['mdbfilepath'] = $this->Parameter->get_value('mdb_server_file_path');
        //$this->load->view('imp_act_upload',$data);
        
        //echo $targetDir;
        $data = array('app_log' => 'NONE');
        $this->session->set_userdata($data);
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $data['notes_import_mdb'] = $this->Parameter->get_value('notes_import_mdb');
            $this->load->view('imp_act_large_upload_cstm',$data);
        }
    }
    
    public function clean_directory() {
        if ($this->session->userdata('username') == '') {
            //echo 'LOGIN REQUIRED';
            $data = array('app_log' => 'LOGIN REQUIRED : clean_directory');
            $this->session->set_userdata($data);
        } else {
            $this->load->helper('file');
            //$targetDir = base_url('files/mdb');
            //'./files/mdb/';
            $targetDir = $this->Parameter->get_value('MDB_SERVER_DIR_PATH_REMOTE');
            if (delete_files($targetDir)) {
                echo 'TRUE';
            } else {
                echo 'FALSE';
            }
        }
    }
    
    /*public function uploader() {
        $this->load->helper('file');
        //$targetDir = base_url('files/mdb');
        $targetDir = './files/mdb/';
        delete_files($targetDir);
        //echo $targetDir;
        $this->load->view('imp_act_large_upload_cstm');
    }
    
    public function uploader2() {
        $this->load->helper('file');
        //$targetDir = base_url('files/mdb');
        $targetDir = './files/mdb/';
        delete_files($targetDir);
        //echo $targetDir;
        $this->load->view('imp_act_large_upload');
    }*/
    
    /*public function do_upload() {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'mdb';
        $config['max_size'] = '2048000';
        //$config['max_width'] = '1024';
        //$config['max_height'] = '768';

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            $error = array('error' => $this->upload->display_errors());
            //$data = array('upload_data' => $this->upload->data());
            print_r($this->upload->data());
            //$this->load->view('upload_form', $error);
        } else {
            $data = array('upload_data' => $this->upload->data());

            print_r($data);
        
            echo $_FILES['upload']['tmp_name'];
            //$this->load->view('upload_success', $data);
        }
    }
    
    public function encrypt_parameter_ftp($hostname,$username,$password) {
        $this->load->library('encrypt');
        
        echo $this->encrypt->encode($hostname).'<br/>';
        echo $this->encrypt->encode($username).'<br/>';
        echo $this->encrypt->encode($password).'<br/>';
    }
    
    public function mdb_get_att2000() {
        $this->load->library('ftp');
        $this->load->library('encrypt');
        
        $config['hostname'] = $this->encrypt->decode($this->Parameter->get_value('FTP_HOSTNAME'));
        $config['username'] = $this->encrypt->decode($this->Parameter->get_value('FTP_USERNAME'));
        $config['password'] = $this->encrypt->decode($this->Parameter->get_value('FTP_PASSWORD'));
        $config['debug'] = TRUE;
        
        echo $this->encrypt->decode($this->Parameter->get_value('FTP_HOSTNAME'));
        echo $this->encrypt->decode($this->Parameter->get_value('FTP_USERNAME'));
        echo $this->encrypt->decode($this->Parameter->get_value('FTP_PASSWORD'));
        echo $this->Parameter->get_value('mdb_local_file_path');
        echo $this->Parameter->get_value('mdb_server_file_path');
        
        $this->ftp->connect($config);

        $this->ftp->upload($this->Parameter->get_value('mdb_local_file_path'), $this->Parameter->get_value('mdb_server_file_path'));

        $this->ftp->close();
    }*/
    
    public function mdb_setting() {
        //$this->mdb_get_att2000();
        //$file_path = "D:\UPJ\Attendance\att2000.mdb";
        //$file_path = "D:\UPJ\Attendance\mdb\att2000.mdb";
        if ($this->session->userdata('username') == '') {
            //echo 'LOGIN REQUIRED';
            $data = array('app_log' => 'LOGIN REQUIRED : mdb_setting');
            $this->session->set_userdata($data);
        } else {
            $file_path = $this->Parameter->get_value('mdb_server_file_path');
        //$file_path = $this->input->post('mdbfilepath');
            //$this->session->set_userdata('import_mdb_file_path', $file_path);
        }
        //echo $file_path;
        
        //echo $this->session->userdata('import_mdb_file_path');
    }
    
    /*public function setting2() {
        //$file_path = "D:\UPJ\Attendance\att2000.mdb";
        //$this->session->set_userdata('import_mdb_file_path', $file_path);
        
        //echo $file_path;
        
        echo $this->session->userdata('import_mdb_file_path');
    }*/
    
    /*public function mdb() {
        //$this->load->view('imp_act');
    }*/

    public function mdb_checkinout() {
        if ($this->session->userdata('username') == '') {
            $data = array('app_log' => 'LOGIN REQUIRED : mdb_checkinout');
            $this->session->set_userdata($data);
            //echo 'LOGIN REQUIRED';
        } else {
            $file_path = $this->Parameter->get_value('mdb_server_file_path');
        //$file_path = $this->session->userdata('import_mdb_file_path');

        //echo $file_path;

        //$file_path = "D:\UPJ\Attendance\att2000.mdb";

        $config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $db_mdb = $this->load->database($config, TRUE);
        $db_mysql = $this->load->database('temporary', TRUE);
        
        //echo "load 2 db finish";
        
        //$qry_mdb = $db_mdb->query("SELECT USERID AS user_id, CHECKTIME AS check_time FROM CHECKINOUT");
        $sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT";
        
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE DATEVALUE(CHECKTIME) >= '12/1/2013' AND DATEVALUE(CHECKTIME) <= '12/31/2013'";
        */
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE USERID >= 116 AND USERID <= 135";
        */
        /*$sql_mdb = "SELECT DEPTID AS dept_id, DEPTNAME AS dept_name, SUPDEPTID AS sup_dept_id, 
            InheritParentSch AS inherit_parent_sch, InheritDeptSch AS inherit_dept_sch, 
            InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
            InLate AS in_late, OutEarly AS out_early, InheritDeptRule AS inherit_dept_rule, 
            MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
            DefaultSchId AS default_sch_id, att, holiday, OverTime AS over_time 
            FROM DEPARTMENTS";*/
        
       /*$sql_mdb = "SELECT USERID AS user_id, Badgenumber AS badge_number, ssn, name, gender, title, 
           pager, BIRTHDAY AS birth_day, HIREDDAY AS hired_day, street, city, state, zip, 
           OPHONE AS o_phone, FPHONE AS f_phone, VERIFICATIONMETHOD AS verification_method, 
           DEFAULTDEPTID AS default_dept_id, SECURITYFLAGS AS security_flags, att, INLATE AS in_late, 
           OUTEARLY AS out_early, overtime, sep, holiday, minzu, password, LUNCHDURATION AS lunch_duration, 
           MVERIFYPASS AS m_verify_pass, photo, notes, privilege, InheritDeptSch AS inherit_dept_sch, 
           InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
           MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
           InheritDeptRule AS inherit_dept_rule, emprivilege, CardNo AS card_no, pin1
           FROM USERINFO";*/
        
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        //echo "query mdb finish next trans";
        
        $db_mysql->trans_start();
        
        //$db_mysql->query('TRUNCATE mdb_checkinout');
        $db_mysql->truncate('mdb_checkinout');
        
        $i = 0;
        
        foreach ($qry_mdb->result() as $row_mdb) {
            $data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'check_time' => $row_mdb->check_time,
                'check_type' => $row_mdb->check_type,
                'verify_code' => $row_mdb->verify_code,
                'sensor_id' => $row_mdb->sensor_id,
                'work_code' => $row_mdb->work_code,
                'sn' => $row_mdb->sn
            );
            
            /*$data_mysql = array(
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
            );*/
            
            /*$data_mysql = array(
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
            );*/
            
            //print_r($data_mysql);
            
            $db_mysql->insert('mdb_checkinout', $data_mysql);
            //$db_mysql->insert('mdb_departments', $data_mysql);
            //$db_mysql->insert('mdb_userinfo', $data_mysql);
            
            //echo "insert data to mysql finish ".$row_mdb->user_id;
            $i++;
        }
        
        echo $i;
        
        $db_mysql->trans_complete();
        
        $db_mdb->close();
        $db_mysql->close();

        //$this->load->view('welcome_message');
        }
    }
    
    public function mdb_userinfo() {
        if ($this->session->userdata('username') == '') {
            //echo 'LOGIN REQUIRED';
            $data = array('app_log' => 'LOGIN REQUIRED : mdb_userinfo');
            $this->session->set_userdata($data);
            show_404();
        } else {
            $file_path = $this->Parameter->get_value('mdb_server_file_path');
        //$file_path = $this->session->userdata('import_mdb_file_path');

        //echo $file_path;

        //$file_path = "D:\UPJ\Attendance\att2000.mdb";

        $config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $db_mdb = $this->load->database($config, TRUE);
        $db_mysql = $this->load->database('temporary', TRUE);
        
        //echo "load 2 db finish";
        
        //$qry_mdb = $db_mdb->query("SELECT USERID AS user_id, CHECKTIME AS check_time FROM CHECKINOUT");
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT";*/
        
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE DATEVALUE(CHECKTIME) >= '12/1/2013' AND DATEVALUE(CHECKTIME) <= '12/31/2013'";
        */
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE USERID >= 116 AND USERID <= 135";
        */
        /*$sql_mdb = "SELECT DEPTID AS dept_id, DEPTNAME AS dept_name, SUPDEPTID AS sup_dept_id, 
            InheritParentSch AS inherit_parent_sch, InheritDeptSch AS inherit_dept_sch, 
            InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
            InLate AS in_late, OutEarly AS out_early, InheritDeptRule AS inherit_dept_rule, 
            MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
            DefaultSchId AS default_sch_id, att, holiday, OverTime AS over_time 
            FROM DEPARTMENTS";*/
        
       $sql_mdb = "SELECT USERID AS user_id, Badgenumber AS badge_number, ssn, name, gender, title, 
           pager, BIRTHDAY AS birth_day, HIREDDAY AS hired_day, street, city, state, zip, 
           OPHONE AS o_phone, FPHONE AS f_phone, VERIFICATIONMETHOD AS verification_method, 
           DEFAULTDEPTID AS default_dept_id, SECURITYFLAGS AS security_flags, att, INLATE AS in_late, 
           OUTEARLY AS out_early, overtime, sep, holiday, minzu, password, LUNCHDURATION AS lunch_duration, 
           MVERIFYPASS AS m_verify_pass, photo, notes, privilege, InheritDeptSch AS inherit_dept_sch, 
           InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
           MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
           InheritDeptRule AS inherit_dept_rule, emprivilege, CardNo AS card_no, pin1
           FROM USERINFO";
        
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        //echo "query mdb finish next trans";
        
        $db_mysql->trans_start();
        
        //$db_mysql->query('TRUNCATE mdb_userinfo');
        $db_mysql->truncate('mdb_userinfo');
        
        $i = 0;
        
        foreach ($qry_mdb->result() as $row_mdb) {
            /*$data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'check_time' => $row_mdb->check_time,
                'check_type' => $row_mdb->check_type,
                'verify_code' => $row_mdb->verify_code,
                'sensor_id' => $row_mdb->sensor_id,
                'work_code' => $row_mdb->work_code,
                'sn' => $row_mdb->sn
            );*/
            
            /*$data_mysql = array(
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
            );*/
            
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
            
            //print_r($data_mysql);
            
            //$db_mysql->insert('mdb_checkinout', $data_mysql);
            //$db_mysql->insert('mdb_departments', $data_mysql);
            $db_mysql->insert('mdb_userinfo', $data_mysql);
            
            //echo "insert data to mysql finish ".$row_mdb->user_id;
            $i++;
        }
        
        echo $i;
        
        $db_mysql->trans_complete();
        
        $db_mdb->close();
        $db_mysql->close();

        //$this->load->view('welcome_message');
        }
    }
    
    public function mdb_departments() {
        if ($this->session->userdata('username') == '') {
            //echo 'LOGIN REQUIRED';
            $data = array('app_log' => 'LOGIN REQUIRED : mdb_departments');
            $this->session->set_userdata($data);
        } else {
            $file_path = $this->Parameter->get_value('mdb_server_file_path');
        //$file_path = $this->session->userdata('import_mdb_file_path');

        //echo $file_path;

        //$file_path = "D:\UPJ\Attendance\att2000.mdb";
        
        //echo $file_path;
        
        $config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $db_mdb = $this->load->database($config, TRUE);
        $db_mysql = $this->load->database('temporary', TRUE);
        
        //echo "load 2 db finish";
        
        //$qry_mdb = $db_mdb->query("SELECT USERID AS user_id, CHECKTIME AS check_time FROM CHECKINOUT");
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT";*/
        
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE DATEVALUE(CHECKTIME) >= '12/1/2013' AND DATEVALUE(CHECKTIME) <= '12/31/2013'";
        */
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE USERID >= 116 AND USERID <= 135";
        */
        $sql_mdb = "SELECT DEPTID AS dept_id, DEPTNAME AS dept_name, SUPDEPTID AS sup_dept_id, 
            InheritParentSch AS inherit_parent_sch, InheritDeptSch AS inherit_dept_sch, 
            InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
            InLate AS in_late, OutEarly AS out_early, InheritDeptRule AS inherit_dept_rule, 
            MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
            DefaultSchId AS default_sch_id, att, holiday, OverTime AS over_time 
            FROM DEPARTMENTS";
        
       /*$sql_mdb = "SELECT USERID AS user_id, Badgenumber AS badge_number, ssn, name, gender, title, 
           pager, BIRTHDAY AS birth_day, HIREDDAY AS hired_day, street, city, state, zip, 
           OPHONE AS o_phone, FPHONE AS f_phone, VERIFICATIONMETHOD AS verification_method, 
           DEFAULTDEPTID AS default_dept_id, SECURITYFLAGS AS security_flags, att, INLATE AS in_late, 
           OUTEARLY AS out_early, overtime, sep, holiday, minzu, password, LUNCHDURATION AS lunch_duration, 
           MVERIFYPASS AS m_verify_pass, photo, notes, privilege, InheritDeptSch AS inherit_dept_sch, 
           InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
           MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
           InheritDeptRule AS inherit_dept_rule, emprivilege, CardNo AS card_no, pin1
           FROM USERINFO";*/
        
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        //echo "query mdb finish next trans";
        
        $db_mysql->trans_start();
        
        //$db_mysql->query('TRUNCATE mdb_departments');
        $db_mysql->truncate('mdb_departments');
        
        $i = 0;
        
        foreach ($qry_mdb->result() as $row_mdb) {
            /*$data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'check_time' => $row_mdb->check_time,
                'check_type' => $row_mdb->check_type,
                'verify_code' => $row_mdb->verify_code,
                'sensor_id' => $row_mdb->sensor_id,
                'work_code' => $row_mdb->work_code,
                'sn' => $row_mdb->sn
            );*/
            
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
            
            /*$data_mysql = array(
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
            );*/
            
            //print_r($data_mysql);
            
            //$db_mysql->insert('mdb_checkinout', $data_mysql);
            $db_mysql->insert('mdb_departments', $data_mysql);
            //$db_mysql->insert('mdb_userinfo', $data_mysql);
            
            //echo "insert data to mysql finish ".$row_mdb->user_id;
            $i++;
        }
        
        echo $i;
        
        $db_mysql->trans_complete();
        
        $db_mdb->close();
        $db_mysql->close();

        //$this->load->view('welcome_message');
        }
    }
    
    public function mdb_process() {
        if ($this->session->userdata('username') == '') {
            //echo 'LOGIN REQUIRED'
            $data = array('app_log' => 'LOGIN REQUIRED : mdb_process');
            $this->session->set_userdata($data);
        } else {
            $user_id = $this->session->userdata('credentials');
        //cek apa data sudah ada di TBL attendance
        //dengan mengecek COL user_id, date
        //1. bandingkan DB temp TAB mdb_checkinout COL user_id dengan DB presensi TAB attendance COL user_id
        //--->> bila semua user_id sama (tidak terjadi penambahan)
        //------:: sql dengan filter max_date
        //--->> bila ada user_id beda (terjadi penambahan)
        //------:: cari user_id yang beda,, sql untuk semua (tanpa filter max_date) 
        
        /*$db_dflt = $this->load->database('default', TRUE);
        $sql_dflt_user_id = "SELECT user_id FROM attendance
            GROUP BY user_id";
        //print($sql_dflt_user_id);
        $qry_dflt_user_id = $db_dflt->query($sql_dflt_user_id);
        $arr_dflt_user_id = array();
        foreach ($qry_dflt_user_id->result() as $row_dflt_user_id) {
            $arr_dflt_user_id[] = $row_dflt_user_id->user_id;
        }
        //print_r($arr_dflt_user_id);
        $db_dflt->close();*/
        
        $db_dflt = $this->load->database('default', TRUE);
        $sql_dflt_max_date = "SELECT user_id,
            MAX(date) AS max_date,
            IF(YEAR(MAX(date)) IS NULL,0,YEAR(MAX(date))) AS year_max_date,
            IF(DAYOFYEAR(MAX(date)) IS NULL,0,DAYOFYEAR(MAX(date))) AS day_max_date
            FROM attendance
            GROUP BY user_id";
        //print($sql_dflt_max_date);
        $qry_dflt_max_date = $db_dflt->query($sql_dflt_max_date);
        $arr_dflt_user_id = array();
        $arr_dflt_max_date = array();
        $index = 0;
        foreach ($qry_dflt_max_date->result() as $row_dflt_max_date) {
            $arr_dflt_user_id[] = $row_dflt_max_date->user_id;
            $arr_dflt_max_date[$index] = new stdClass();
            $arr_dflt_max_date[$index]->user_id = $row_dflt_max_date->user_id;
            $arr_dflt_max_date[$index]->year_max_date = $row_dflt_max_date->year_max_date;
            $arr_dflt_max_date[$index]->day_max_date = $row_dflt_max_date->day_max_date;
            $index++;
        }
        //print_r($arr_dflt_max_date);
        $db_dflt->close();
        
        $db_temp = $this->load->database('temporary', TRUE);
        $sql_temp_user_id = "SELECT user_id FROM mdb_checkinout
            GROUP BY user_id";
        //print($sql_temp_user_id);
        $qry_temp_user_id = $db_temp->query($sql_temp_user_id);
        $arr_diff_user_id = array();
        foreach ($qry_temp_user_id->result() as $row_temp_user_id) {
            if (!in_array($row_temp_user_id->user_id, $arr_dflt_user_id)) {
                //$arr_diff_user_id[] = $row_temp_user_id->user_id;
                $arr_dflt_max_date[$index] = new stdClass();
                $arr_dflt_max_date[$index]->user_id = $row_temp_user_id->user_id;
                $arr_dflt_max_date[$index]->year_max_date = '0';
                $arr_dflt_max_date[$index]->day_max_date = '0';
                $index++;
            }
        }
        //print_r($arr_diff_user_id);
        
        $arr_temp_all_import = array();
        foreach ($arr_dflt_max_date as $row_max_date) {
            if (($row_max_date->year_max_date == '0') && ($row_max_date->day_max_date == '0')) {
                $fltr_max_date = "";
            } else {
                $fltr_max_date = " AND DATE(check_time) > MAKEDATE($row_max_date->year_max_date,$row_max_date->day_max_date)";
            }
            $sql_temp_import = "SELECT user_id,
                DATE(check_time) AS date,
                MAKETIME(HOUR(MIN(check_time)),MINUTE(MIN(check_time)),00) AS min_time,
                MAKETIME(HOUR(MAX(check_time)),MINUTE(MAX(check_time)),00) AS max_time
                FROM mdb_checkinout
                WHERE user_id = $row_max_date->user_id".$fltr_max_date
                ." GROUP BY user_id, DATE(check_time)
                ORDER BY user_id, DATE(check_time)";
            //print($sql_temp_user_id);
            $qry_temp_import = $db_temp->query($sql_temp_import);
            //print_r($qry_temp_import);
            //echo $row_max_date->user_id.' '.$sql_temp_import.' >>> '.$qry_temp_import->num_rows().'<br/>';
            foreach ($qry_temp_import->result() as $row_temp_import) {
                $arr_temp_all_import[] = array(
                    'created_by' => $user_id,
                    'user_id' => $row_temp_import->user_id,
                    'date' => $row_temp_import->date,
                    'min_time' => $row_temp_import->min_time,
                    'max_time' => $row_temp_import->max_time
                );
            }
        }
        $db_temp->close();
        
        if (sizeof($arr_temp_all_import) > 0) {
            $db_dflt = $this->load->database('default', TRUE);
            $db_dflt->insert_batch('attendance', $arr_temp_all_import);
            $db_dflt->close();
        }
        
        echo sizeof($arr_temp_all_import);
        
        $db_temp = $this->load->database('temporary', TRUE);
        
        $sql_temp_import_dept = "SELECT * FROM mdb_departments";
            //print($sql_temp_user_id);
        $qry_temp_import_dept = $db_temp->query($sql_temp_import_dept);
            //print_r($qry_temp_import);
            //echo $row_max_date->user_id.' '.$sql_temp_import.' >>> '.$qry_temp_import->num_rows().'<br/>';
        foreach ($qry_temp_import_dept->result() as $row_temp_import_dept) {
            $arr_temp_all_import_dept[] = array(
                'created_by' => $user_id,
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
        
        $sql_temp_import_user = "SELECT * FROM mdb_userinfo";
            //print($sql_temp_user_id);
        $qry_temp_import_user = $db_temp->query($sql_temp_import_user);
            //print_r($qry_temp_import);
            //echo $row_max_date->user_id.' '.$sql_temp_import.' >>> '.$qry_temp_import->num_rows().'<br/>';
        foreach ($qry_temp_import_user->result() as $row_temp_import_user) {
            $arr_temp_all_import_user[] = array(
                'created_by' => $user_id,
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
        
        if ((sizeof($arr_temp_all_import_dept) > 0) || (sizeof($arr_temp_all_import_user) > 0)) {
            $db_dflt = $this->load->database('default', TRUE);
            $db_dflt->truncate('department');
            $db_dflt->insert_batch('department', $arr_temp_all_import_dept);
            $db_dflt->truncate('userinfo');
            $db_dflt->insert_batch('userinfo', $arr_temp_all_import_user);
            $db_dflt->close();
        }
        
        echo ';'.sizeof($arr_temp_all_import_dept);
        echo ';'.sizeof($arr_temp_all_import_user);
        
        }
        //print_r($arr_temp_all_import);
        
        /*$db_dflt = $this->load->database('default', TRUE);
        foreach ($arr_dflt_user_id as $a_user_id) {
            $sql_dflt_max_date = "SELECT MAX(date) AS max_date,
                IF(YEAR(MAX(date)) IS NULL,0,YEAR(MAX(date))) AS year_max_date,
                IF(DAYOFYEAR(MAX(date)) IS NULL,0,DAYOFYEAR(MAX(date))) AS day_max_date
                FROM attendance
                WHERE user_id = $a_user_id";
            print($sql_dflt_max_date);
            $qry_dflt_max_date = $db_dflt->query($sql_dflt_max_date);
            $row_dflt_max_date = $qry_dflt_max_date->row(); 
            
            
            $qry_dflt_user_id = $db_dflt->query($sql_dflt_user_id);
            $arr_dflt_user_id = array();
            foreach ($qry_dflt_user_id->result() as $row_dflt_user_id) {
                $arr_dflt_user_id[] = $row_dflt_user_id->user_id;
            }
        }
        $db_dflt->close();*/
    }
    
    /*public function mdb_process() {
        if ($this->session->userdata('username') == '') {
            echo 'LOGIN REQUIRED';
        } else {

            //$db_dflt = $this->load->database('default', TRUE);
            $db_temp = $this->load->database('temporary', TRUE);

            $sql_temp = "SELECT user_id,
            DATE(check_time) AS date,
            MAKETIME(HOUR(MIN(check_time)),MINUTE(MIN(check_time)),00) AS min_time,
            MAKETIME(HOUR(MAX(check_time)),MINUTE(MAX(check_time)),00) AS max_time
            FROM mdb_checkinout";
            
            
            
            //cek apa data sudah ada di TBL attendance
            //dengan mengecek COL user_id, date
            //1. bandingkan DB temp COL user_id dengan DB presensi COL user_id
            //--->> bila jumlah user_id sama (tidak terjadi penambahan)
            //------:: sql dengan filter max_date
            //--->> bila jumlah user_id beda (terjadi penambahan)
            //------:: cari user_id yang beda,, sql untuk semua (tanpa filter max_date) 
            //2. 
           
            
            $sql_temp = $sql_temp." GROUP BY user_id, DATE(check_time)
                ORDER BY user_id, DATE(check_time)";
            
            $qry_temp = $db_temp->query($sql_temp);

            $db_temp->close();

            //echo $sql_temp;

            $db_dflt = $this->load->database('default', TRUE);

            //$result = $this->db->query('TRUNCATE attendance');
            //$db_dflt->truncate('attendance');

            //echo $result;

            $db_dflt->trans_start();

            $data_dflt = array();

            //print_r($data_dflt);

            foreach ($qry_temp->result() as $row_temp) {

                $data_dflt = array(
                    'user_id' => $row_temp->user_id,
                    'date' => $row_temp->date,
                    'min_time' => $row_temp->min_time,
                    'max_time' => $row_temp->max_time
                );

                $db_dflt->insert('attendance', $data_dflt);
                //$db_mysql->insert('mdb_userinfo', $data_mysql);
                //echo "insert data to mysql finish ".$row_mdb->user_id;
            }

            //print_r($data_dflt);

            $db_dflt->trans_complete();

            //$db_temp->close();
            //$this->load->view('welcome_message');
        
        
        }
    }*/
}