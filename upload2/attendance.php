<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Attendance extends CI_Controller {
    
    function __construct() {
        parent::__construct();

        // To load the CI benchmark and memory usage profiler - set 1==1.
        if (1 == 2) {
            $sections = array(
                'benchmarks' => TRUE, 'memory_usage' => TRUE,
                'config' => FALSE, 'controller_info' => FALSE, 'get' => FALSE, 'post' => FALSE, 'queries' => FALSE,
                'uri_string' => FALSE, 'http_headers' => FALSE, 'session_data' => FALSE
            );
            $this->output->set_profiler_sections($sections);
            $this->output->enable_profiler(TRUE);
        }

        // Load required CI libraries and helpers.
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');

        // IMPORTANT! This global must be defined BEFORE the flexi auth library is loaded! 
        // It is used as a global that is accessible via both models and both libraries, without it, flexi auth will not work.
        $this->auth = new stdClass;

        // Load 'standard' flexi auth library by default.
        $this->load->library('flexi_auth');

        // Check user is logged in via either password or 'Remember me'.
        // Note: Allow access to logged out users that are attempting to validate a change of their email address via the 'update_email' page/method.
        if (!$this->flexi_auth->is_logged_in()) {
            // Set a custom error message.
            $this->flexi_auth->set_error_message('You must login to access this area.', TRUE);
            $this->session->set_flashdata('message', $this->flexi_auth->get_messages());
            redirect('user');
        }

        // Note: This is only included to create base urls for purposes of this demo only and are not necessarily considered as 'Best practice'.
        $this->load->vars('base_url', 'http://server.upj/presensi2/');
        $this->load->vars('includes_dir', 'http://server.upj/presensi2/includes/');
        $this->load->vars('current_url', $this->uri->uri_to_assoc(1));

        // Define a global variable to store data that is then used by the end view page.
        $this->data = null;
    }
        
    public function index() {
        $this->report();
    }
    
    public function entry() {
        $this->filter_ent();
    }
    
    var $filter_ent_alias = 'entry';
    public function filter_ent() {
        if (!$this->flexi_auth->is_privileged('ins_ket')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $this->load->helper('custom_string');
        $this->load->model('Personnel_model');
        $data['personnel_option'] = get_array_value_do_ucwords($this->Personnel_model->get_all_personnel_name());
        
        $this->load->helper('custom_date');
        $data['month_option'] = get_all_month_name();
        
        $this->load->model('Attendance_model');
        $data['year_option'] = $this->Attendance_model->get_all_year();
        
        // Get any status message that may have been set.
	$data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];
        $data['message_type'] = (! isset($this->data['message_type'])) ? $this->session->flashdata('message_type') : $this->data['message_type'];
        
        $data['form_action_url'] = site_url('attendance/'.$this->personnel_ent_alias);
        
        $this->load->view('attendance/ent_filter',$data);
    }
    
    public function entry1($personnel = NULL, $year = NULL, $month = NULL) {
        $this->personnel_ent($personnel, $year, $month);
    }
    
    var $personnel_ent_alias = 'entry1';
    public function personnel_ent($personnel = NULL, $year = NULL, $month = NULL) {
        if (!$this->flexi_auth->is_privileged('ins_ket')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $this->load->model('Attendance_model');
        $arr_ket[0] = '';
        
        if (($this->input->post('personnel') != '') && ($this->input->post('year') != '') && ($this->input->post('month') != '')) {
            $data['personnel'] = $this->input->post('personnel');
            $data['year'] = $this->input->post('year');
            $data['month'] = $this->input->post('month');
        } else if (($personnel != NULL) && ($year != NULL) && ($month != NULL)) {
            $data['personnel'] = $personnel;
            $data['year'] = $year;
            $data['month'] = $month;
        } else {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect('attendance/'.$this->filter_ent_alias);
        }
        
        $data['keterangan_option'] = $this->Attendance_model->get_all_keterangan($arr_ket);
        $data['attendance'] = $this->Attendance_model->get_attendance_data_personnel_monthly($data['personnel'],$data['year'],$data['month']);
        $data['summary_attendance'] = $this->Attendance_model->get_summary_attendance_data_personnel_monthly($data['personnel'],$data['year'],$data['month']);
        
        if ($data['attendance'] == NULL) {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'warning');
            redirect('attendance/'.$this->filter_ent_alias);
        }
        
        $this->load->helper('custom_string');
        $this->load->model('Personnel_model');
        $data['personnel_name'] = do_ucwords($this->Personnel_model->get_personnel_name($data['personnel']));
        
        $this->load->model('Department_model');
        $data['department_name'] = do_ucwords($this->Department_model->get_department_name($this->Personnel_model->get_dept_id($data['personnel'])));
        
        $this->load->helper('custom_date');
        $data['month_year'] = get_month_name($data['month']).' '.$data['year'];
        
        $data['form_action_url'] = site_url('attendance/save_ent');
        
        $data['export_xls1_url'] = site_url('export/xls1/'.$data['personnel'].'/'.$data['year'].'/'.$data['month']);
        
        $data['summary_of_keterangan'] = $this->Attendance_model->get_summary_of_keterangan($data['personnel'],$data['year'],$data['month']);
        
        $this->load->view('attendance/ent_personnel',$data);
    }
    
    public function save_ent() {
        if (!$this->flexi_auth->is_privileged('ins_ket')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $this->load->model('Attendance_model');
        $personnel = $this->input->post('personnel');
        $year = $this->input->post('year');
        $month = $this->input->post('month');
        $ket = $this->input->post('keterangan');
        $success = $this->Attendance_model->insert_keterangan($personnel,$year,$month,$ket);
        //var_dump($success); //AMRNOTE: FALSE == 100
        redirect('attendance/'.$this->personnel_ent_alias.'/'.$personnel.'/'.$year.'/'.$month);
    }
    
    //menu report
    /*public function report() {
        $this->reporta();
    }*/
    
    public function reporta() {
        $this->filter_prsn_mnth_rpt();
    }
    
    var $filter_prsn_mnth_rpt_alias = 'reporta';
    public function filter_prsn_mnth_rpt() {
        if (!$this->flexi_auth->is_privileged('vw_mnth_prsn_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $this->load->helper('custom_string');
        $this->load->model('Personnel_model');
        $data['personnel_option'] = get_array_value_do_ucwords($this->Personnel_model->get_all_personnel_name(array('ALL' => '-- Semua Karyawan/Dosen --')));
        
        $this->load->helper('custom_date');
        $data['month_option'] = get_all_month_name();
        
        $this->load->model('Attendance_model');
        $data['year_option'] = $this->Attendance_model->get_all_year();
        
        // Get any status message that may have been set.
	$data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];
        $data['message_type'] = (! isset($this->data['message_type'])) ? $this->session->flashdata('message_type') : $this->data['message_type'];
        
        $data['form_action_url'] = site_url('attendance/'.$this->prsn_mnth_rpt_alias);
        
        $this->load->view('attendance/rpt_filter_prsn_mnth',$data);
    }
    
    public function reportb() {
        $this->filter_dept_year_rpt();
    }
    
    var $filter_dept_year_rpt_alias = 'reportb';
    public function filter_dept_year_rpt() {
        if (!$this->flexi_auth->is_privileged('vw_year_dept_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $this->load->helper('custom_string');
        $this->load->model('Department_model');
        $data['department_option'] = get_array_value_do_ucwords($this->Department_model->get_all_department_name(array('ALL' => '-- Semua Bagian/Prodi --')));
        
        $this->load->helper('custom_date');
        $data['month_option'] = get_all_month_name();
        
        $this->load->model('Attendance_model');
        $data['year_option'] = $this->Attendance_model->get_all_year();
        
        // Get any status message that may have been set.
	$data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];
        $data['message_type'] = (! isset($this->data['message_type'])) ? $this->session->flashdata('message_type') : $this->data['message_type'];
        
        $data['form_action_url'] = site_url('attendance/'.$this->dept_year_rpt_alias);
        
        $this->load->view('attendance/rpt_filter_dept_year',$data);
    }
    
    public function report1($personnel = NULL, $year = NULL, $month = NULL) {
        $this->prsn_mnth_rpt($personnel, $year, $month);
    }
    
    var $prsn_mnth_rpt_alias = 'report1';
    public function prsn_mnth_rpt($personnel = NULL, $year = NULL, $month = NULL) {
        if (!$this->flexi_auth->is_privileged('vw_mnth_prsn_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $this->load->model('Attendance_model');
        $arr_ket[0] = '';
        
        if (($this->input->post('personnel') != '') && ($this->input->post('year') != '') && ($this->input->post('month') != '')) {
            $data['personnel'] = $this->input->post('personnel');
            $data['year'] = $this->input->post('year');
            $data['month'] = $this->input->post('month');
        } else if (($personnel != NULL) && ($year != NULL) && ($month != NULL)) {
            $data['personnel'] = $personnel;
            $data['year'] = $year;
            $data['month'] = $month;
        } else {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect('attendance/'.$this->filter_prsn_mnth_rpt_alias);
        }
        
        if ($data['personnel'] == 'ALL') {
            redirect('attendance/'.$this->all_prsn_month_rpt_alias.'/'.$data['year'].'/'.$data['month']);
        }
        
        $data['keterangan_option'] = $this->Attendance_model->get_all_keterangan($arr_ket);
        $data['attendance'] = $this->Attendance_model->get_attendance_data_personnel_monthly($data['personnel'],$data['year'],$data['month']);
        $data['summary_attendance'] = $this->Attendance_model->get_summary_attendance_data_personnel_monthly($data['personnel'],$data['year'],$data['month']);
        
        if ($data['attendance'] == NULL) {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'warning');
            redirect('attendance/'.$this->filter_prsn_mnth_rpt_alias);
        }
        
        $this->load->helper('custom_string');
        $this->load->model('Personnel_model');
        $data['personnel_name'] = do_ucwords($this->Personnel_model->get_personnel_name($data['personnel']));
        
        $this->load->model('Department_model');
        $data['department_name'] = do_ucwords($this->Department_model->get_department_name($this->Personnel_model->get_dept_id($data['personnel'])));
        
        $this->load->helper('custom_date');
        $data['month_year'] = get_month_name($data['month']).' '.$data['year'];
        
        //$data['form_action_url'] = site_url('attendance/save_ent');
        
        $data['export_xls1_url'] = site_url('export/xls1/'.$data['personnel'].'/'.$data['year'].'/'.$data['month']);
        
        $data['summary_of_keterangan'] = $this->Attendance_model->get_summary_of_keterangan($data['personnel'],$data['year'],$data['month']);
        
        $this->load->view('attendance/rpt_prsn_mnth',$data);
    }
    
    public function report2($dept = NULL, $year = NULL) {
        $this->dept_year_rpt($dept, $year);
    }
    
    var $dept_year_rpt_alias = 'report2';
    public function dept_year_rpt($dept = NULL, $year = NULL) {
        if (!$this->flexi_auth->is_privileged('vw_year_dept_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        if (($this->input->post('department') != '') && ($this->input->post('year') != '')) {
            $dept = $this->input->post('department');
            $year = $this->input->post('year');
        } else if (($dept != NULL) && ($year != NULL)) {
            $dept = $dept;
            $year = $year;
        } else {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect('attendance/'.$this->filter_dept_year_rpt_alias);
        }
        
        if ($dept == 'ALL') {
            redirect('attendance/'.$this->all_dept_year_rpt_alias.'/'.$year);
        } else {
            redirect('export/xls_rpt_attendance_department_yearly/'.$dept.'/'.$year);
        }
    }
    
    public function report2a($year = NULL) {
        $this->all_dept_year_rpt($year);
    }
    
    var $all_dept_year_rpt_alias = 'report2a';
    public function all_dept_year_rpt($year = NULL) {
        if (!$this->flexi_auth->is_privileged('vw_year_dept_rpt_all')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        if (empty($year)) {
            redirect('attendance/'.$this->filter_dept_year_rpt_alias);
        }
        
        $this->load->helper('custom_string');
        //DELETE ALL FILES IN ./XLS/YDAR/
        $this->load->helper('file');
        delete_files($this->Parameter->get_value('FOLDER_ON_SERVER_FOR_YDAR'));
        write_file($this->Parameter->get_value('FOLDER_ON_SERVER_FOR_YDAR').'/index.html','');
        
        $this->load->model('Department_model');
        $arr_department = $this->Department_model->get_all_department_id();
        $arr_dept_name = $this->Department_model->get_all_department_name();
        
        $data['arr_controllers'] = "[";
        $data['arr_interactive'] = "[";
        
        foreach ($arr_department as $dept_id) {
            $data['arr_controllers'] = $data['arr_controllers']."'".base_url('export/xls_rpt_attendance_department_yearly/'.$dept_id.'/'.$year.'/0')."',";
            $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Creating Yearly \"".do_ucwords($arr_dept_name[$dept_id])."\" Attendance Report', after: 'Yearly \"".do_ucwords($arr_dept_name[$dept_id])."\" Attendance Report Created'},";
        }
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('attendance/list_all_dept_year_rpt')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxDir', printr: 'yes', hidingdivid:'ajaxLog', hidingdiv: 'yes'},";
        
        $data['arr_controllers'] = substr($data['arr_controllers'], 0, -1)."]";
        $data['arr_interactive'] = substr($data['arr_interactive'], 0, -1)."]";
        
        //foreach ($arr_department as $dept_id) {
        //    $this->xls_rpt_attendance_department_yearly($dept_id, $year, 0);
        //}
        $data['ajaximg'] = "' <i class=\"icon-spinner icon-spin\"></i>'";
        
        $this->load->view('attendance/rpt_dept_year_all',$data);
    }
    
    public function report1a($year = NULL, $month = NULL) {
        $this->all_prsn_month_rpt($year,$month);
    }
    
    var $all_prsn_month_rpt_alias = 'report1a';
    public function all_prsn_month_rpt($year = NULL, $month = NULL) {
        if (!$this->flexi_auth->is_privileged('vw_mnth_prsn_rpt_all')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        if (empty($year) || empty($month)) {
            redirect('attendance/'.$this->filter_prsn_mnth_rpt_alias);
        }
        
        $this->load->helper('custom_string');
        //DELETE ALL FILES IN ./XLS/YDAR/
        $this->load->helper('file');
        delete_files($this->Parameter->get_value('FOLDER_ON_SERVER_FOR_MPAR'));
        write_file($this->Parameter->get_value('FOLDER_ON_SERVER_FOR_MPAR').'/index.html','');
        
        $this->load->model('Department_model');
        $arr_department = $this->Department_model->get_all_department_id();
        $arr_dept_name = $this->Department_model->get_all_department_name();
        
        $data['arr_controllers'] = "[";
        $data['arr_interactive'] = "[";
        
        foreach ($arr_department as $dept_id) {
            $data['arr_controllers'] = $data['arr_controllers']."'".base_url('export/xls_rpt_attendance_prsn_mnth_in_dept/'.$dept_id.'/'.$year.'/'.$month)."',";
            $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Creating Monthly Personnel In \"".do_ucwords($arr_dept_name[$dept_id])."\" Attendance Report', after: 'Monthly Personnel In \"".do_ucwords($arr_dept_name[$dept_id])."\" Attendance Report Created'},";
        }
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('attendance/list_all_prsn_mnth_rpt')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxDir', printr: 'yes', hidingdivid:'ajaxLog', hidingdiv: 'yes'},";
        
        $data['arr_controllers'] = substr($data['arr_controllers'], 0, -1)."]";
        $data['arr_interactive'] = substr($data['arr_interactive'], 0, -1)."]";
        
        //foreach ($arr_department as $dept_id) {
        //    $this->xls_rpt_attendance_department_yearly($dept_id, $year, 0);
        //}
        $data['ajaximg'] = "' <i class=\"icon-spinner icon-spin\"></i>'";
        
        $this->load->view('attendance/rpt_prsn_mnth_all',$data);
    }
    
    public function list_all_dept_year_rpt() {
        //AMRNOTE: AJAX RESPONSE
        if (!$this->flexi_auth->is_privileged('vw_year_dept_rpt_all')) {
            echo '<p class="message dismissible error">You do not have enough privileges.</p>';
            exit();
        }
        
        $zip = $this->Parameter->get_value('DOWNLOAD_ZIP_FOR_YDAR');
        $this->load->helper('custom_string');
        $this->load->helper('directory');
        
        $this->load->library('zip');
        
        $folder_ydar = $this->Parameter->get_value('FOLDER_ON_SERVER_FOR_YDAR');
        
        $this->load->model('Department_model');
        $arr_dept_name = $this->Department_model->get_all_department_name();
        
        $map = directory_map($folder_ydar, 1);
        
        $ol = '<ol class="list">';
        foreach ($map as $value) {
            if (($value == 'index.html') || (substr($value,(sizeof($value)-4)) == 'zip')) {
                continue;
            }
            $arr_value = explode('_',$value);
            $ol = $ol.'<li class="info"><a href="'.base_url($folder_ydar.'/'.$value).'">'.do_ucwords($arr_dept_name[substr($arr_value[0],4)]).'</a></li>';
            if ($zip) {
                $this->zip->read_file($folder_ydar.'/'.$value);
            }
        }
        //$this->zip->read_dir($folder_ydar.'/');
        
        if ($zip == 'TRUE') {
            $zip_name = 'YDAR'.date("YmdHis").'.zip';
            $this->zip->archive($folder_ydar.'/'.$zip_name);
            echo '<a href="'.base_url($folder_ydar.'/'.$zip_name).'" role="button" class="gap-bottom">Download All</a>';
        }
        $ol = $ol."</ol>";
        
        echo $ol;
    }
    
    public function list_all_prsn_mnth_rpt() {
        //AMRNOTE: AJAX RESPONSE
        if (!$this->flexi_auth->is_privileged('vw_mnth_prsn_rpt_all')) {
            echo '<p class="message dismissible error">You do not have enough privileges.</p>';
            exit();
        }
        
        $zip = $this->Parameter->get_value('DOWNLOAD_ZIP_FOR_MPAR');
        $this->load->helper('custom_string');
        $this->load->helper('directory');
        
        $this->load->library('zip');
        
        $folder_mpar = $this->Parameter->get_value('FOLDER_ON_SERVER_FOR_MPAR');
        
        $this->load->model('Department_model');
        $arr_dept_name = $this->Department_model->get_all_department_name();
        
        $map = directory_map($folder_mpar, 1);
        
        $ol = '<ol class="list">';
        foreach ($map as $value) {
            if (($value == 'index.html') || (substr($value,(sizeof($value)-4)) == 'zip')) {
                continue;
            }
            $arr_value = explode('_',$value);
            $ol = $ol.'<li class="info"><a href="'.base_url($folder_mpar.'/'.$value).'">'.do_ucwords($arr_dept_name[substr($arr_value[0],4)]).'</a></li>';
            if ($zip) {
                $this->zip->read_file($folder_mpar.'/'.$value);
            }
        }
        //$this->zip->read_dir($folder_ydar.'/');
        
        if ($zip == 'TRUE') {
            $zip_name = 'MPAR'.date("YmdHis").'.zip';
            $this->zip->archive($folder_mpar.'/'.$zip_name);
            echo '<a href="'.base_url($folder_mpar.'/'.$zip_name).'" role="button" class="gap-bottom">Download All</a>';
        }
        $ol = $ol."</ol>";
        
        echo $ol;
    }    
}