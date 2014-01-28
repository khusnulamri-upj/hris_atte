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
    public function report() {
        $this->load->view('layout');
    }
    
    public function reporta() {
        $this->filter_prsn_mnth_rpt();
    }
    
    var $filter_prsn_mnth_rpt_alias = 'reporta';
    public function filter_prsn_mnth_rpt() {
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
        
        $data['form_action_url'] = site_url('attendance/'.$this->filter_prsn_mnth_rpt_alias);
        
        $this->load->view('attendance/rpt_filter_prsn_mnth',$data);
    }
    
    public function personnel_monthly_rpt() {
        $this->load->model('Attendance_model');
        $arr_ket[0] = '';
        $data['keterangan_option'] = $this->Attendance_model->get_all_keterangan($arr_ket);
        $this->load->view('rpt_personnel_monthly',$data);
    }
    
    public function filter_dept_year_rpt() {
        $this->load->view('layout');
    }
    
    public function department_yearly_rpt() {
        $this->load->view('layout');
    }

}