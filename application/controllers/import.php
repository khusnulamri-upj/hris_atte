<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends CI_Controller {

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
        /*$this->load->vars('base_url', 'http://localhost/hris_att/');
        $this->load->vars('includes_dir', 'http://localhost/hris_att/includes/');
        $this->load->vars('current_url', $this->uri->uri_to_assoc(1));*/

        // Define a global variable to store data that is then used by the end view page.
        $this->data = null;
    }
    
    //public function index() {
        //INTERFACE
        /*if (!$this->flexi_auth->is_privileged('imp_mdb')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }*/
        
        /*f(file_exists($this->Parameter->get_value('FILE_ON_LOCAL_FOR_MDB'))) {
            $data['file_is_exist'] = '<p class="message success">MDB Files is exist on local computer.</p></br>';
            $data['button_disabled'] = '';
        } else {
            $data['file_is_exist'] = '<p class="message warning">MDB Files is not on local computer.</p></br>';
            $data['button_disabled'] = ' disabled="disabled"';
        }
        
        $data['arr_controllers'] = "[";
        $data['arr_interactive'] = "[";
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_transfer')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Transferring MDB Data To Server', after: 'Transferring MDB Data Finished'},";
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_get_data')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Importing MDB Data To Database', after: 'Importing MDB Data Finished'},";
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_process_data')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Processing MDB Data', after: 'Processing MDB Data Finished'},";
        
        $data['arr_controllers'] = substr($data['arr_controllers'], 0, -1)."]";
        $data['arr_interactive'] = substr($data['arr_interactive'], 0, -1)."]";
        
        $data['ajaximg'] = "' <i class=\"icon-spinner icon-spin\"></i>'";
        
        $this->load->view('attendance/import_vw',$data);
    }
    
    public function mdb_transfer() {
        //TO SERVER VIA FTP
        $this->load->library('ftp');
        
        $config['hostname'] = $this->Parameter->get_value('FTP_HOSTNAME_FOR_MDB');
        $config['username'] = $this->Parameter->get_value('FTP_USERNAME_FOR_MDB');
        $config['password'] = $this->Parameter->get_value('FTP_PASSWORD_FOR_MDB');
        $config['debug'] = TRUE;

        $this->ftp->connect($config);
        
        if(file_exists($this->Parameter->get_value('REMOTE_FILE_ON_SERVER_FOR_MDB'))) {
            $this->ftp->delete_file($this->Parameter->get_value('REMOTE_FILE_ON_SERVER_FOR_MDB'));
        }
        $this->ftp->upload($this->Parameter->get_value('FILE_ON_LOCAL_FOR_MDB'), $this->Parameter->get_value('REMOTE_FILE_ON_SERVER_FOR_MDB'));

        $this->ftp->close();
    }*/
    
    public function index() {
        //INTERFACE
        /*if (!$this->flexi_auth->is_privileged('imp_mdb')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }*/
        
        if(file_exists($this->Parameter->get_value('FILE_ON_LOCAL_FOR_MDB'))) {
            $data['file_is_exist'] = '<p class="message success">MDB Files is exist on local computer.</p></br>';
            $data['button_disabled'] = '';
        } else {
            $data['file_is_exist'] = '<p class="message warning">MDB Files is not on local computer.</p></br>';
            $data['button_disabled'] = ' disabled="disabled"';
        }
        
        $data['arr_controllers'] = "[";
        $data['arr_interactive'] = "[";
        
        /*$data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_transfer')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Transferring MDB Data To Server', after: 'Transferring MDB Data Finished'},";*/
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_get_data')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Importing MDB Data To Database', after: 'Importing MDB Data Finished'},";
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_files_operation')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: ''},";
        
        $data['arr_controllers'] = $data['arr_controllers']."'".base_url('import/mdb_process_data')."',";
        $data['arr_interactive'] = $data['arr_interactive']."{divid: 'ajaxLog', before: 'Processing MDB Data', after: 'Processing MDB Data Finished'},";
        
        $data['arr_controllers'] = substr($data['arr_controllers'], 0, -1)."]";
        $data['arr_interactive'] = substr($data['arr_interactive'], 0, -1)."]";
        
        $data['ajaximg'] = "' <i class=\"icon-spinner icon-spin\"></i>'";
        
        /*require_once '/assets/location.php';
        
        if (file_exists($mdb_host_location.$mdb_remote_location.DIRECTORY_SEPARATOR.$mdb_remote_filename)) {
            $data['file_exist'] = 'File '.$mdb_remote_filename.' is exist in server.';
        } else {
            $data['file_exist'] = 'File '.$mdb_remote_filename.' is not exist in server.';
        }*/
        
        $this->load->view('attendance/import_vw_plupload',$data);
    }
    
    /*public function mdb_transfer() {
        //TO SERVER VIA FTP
        $this->load->library('ftp');
        
        $config['hostname'] = $this->Parameter->get_value('FTP_HOSTNAME_FOR_MDB');
        $config['username'] = $this->Parameter->get_value('FTP_USERNAME_FOR_MDB');
        $config['password'] = $this->Parameter->get_value('FTP_PASSWORD_FOR_MDB');
        $config['debug'] = TRUE;

        $this->ftp->connect($config);
        
        if(file_exists($this->Parameter->get_value('REMOTE_FILE_ON_SERVER_FOR_MDB'))) {
            $this->ftp->delete_file($this->Parameter->get_value('REMOTE_FILE_ON_SERVER_FOR_MDB'));
        }
        $this->ftp->upload($this->Parameter->get_value('FILE_ON_LOCAL_FOR_MDB'), $this->Parameter->get_value('REMOTE_FILE_ON_SERVER_FOR_MDB'));

        $this->ftp->close();
    }*/
    
    public function mdb_files_operation() {
        require_once '/assets/location.php';
        //$file_path = '.'.$mdb_remote_location.DIRECTORY_SEPARATOR.$mdb_remote_filename;
        $file_path = $mdb_host_location.$mdb_remote_location.DIRECTORY_SEPARATOR.$mdb_remote_filename;
        //$file_path = str_replace(DIRECTORY_SEPARATOR, '/', $file_path);
        //echo $file_path;
        unlink($file_path);
    }
    
    public function mdb_get_data() {
        //TO DB TEMPORARY
        $this->load->model('Import_model');
        $row_checkinout = $this->Import_model->get_checkinout();
        $row_userinfo = $this->Import_model->get_userinfo();
        $row_department = $this->Import_model->get_department();
        echo 'ct'.$row_checkinout.'ut'.$row_userinfo.'dt'.$row_department;
    }
    
    public function mdb_process_data() {
        //TO DB PRIMARY
        $this->load->model('Import_model');
        $row_ins_checkinout = $this->Import_model->process_checkinout();
        $row_ins_userinfo = $this->Import_model->process_userinfo();
        $row_ins_department = $this->Import_model->process_department();
        echo 'cd'.$row_ins_checkinout.'ud'.$row_ins_userinfo.'dd'.$row_ins_department;
    }
    
    public function mdb_existing_file() {
        require_once '/assets/location.php';
        
        if (file_exists($mdb_host_location.$mdb_remote_location.DIRECTORY_SEPARATOR.$mdb_remote_filename)) {
            echo '1||<pre><a href="#" id="existingfilerefresh" style="text-decoration:none; color: #009900;" onclick="checkFile();"><i class=" icon-ok"></i> File '.$mdb_remote_filename.' is exist in server.</a></pre>';
        } else {
            echo '0||<pre><a href="#" id="existingfilerefresh" style="text-decoration:none; color: #990000;" onclick="checkFile();"><i class="icon-exclamation"></i> File '.$mdb_remote_filename.' is not exist in server.</a></pre>';
        }
    }
    
    public function run_jftp_attendance() {
        chdir('C:\Users\Amri\Desktop');
        exec('FTPAttendance.jar');
    }
}