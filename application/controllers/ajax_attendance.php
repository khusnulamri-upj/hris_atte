<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ajax_attendance extends CI_Controller {
    
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
        
    public function index() {
        $this->holidays_list(date("Y"));
    }
    
    public function holidays_list($tahun) {
        if (!$this->flexi_auth->is_privileged('vw_holidays')) {
            //$this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            echo "You do not have enough privileges";
            exit();            
        }
        
        $this->load->model('Attendance_model');
        
        $holidays = $this->Attendance_model->get_holidays_list_in_a_year($tahun);
        //print_r($holidays);
        
        if ($holidays !== NULL) {
            echo '<div class="one whole tablelike padded">';

            echo '<div class="equalize row">';
            //echo '<div class="one eighth half-padded align-center">No.</div>';
            echo '<div class="two tenth small-tablet half-padded align-center">Tanggal</div>';
            echo '<div class="three tenth small-tablet half-padded align-center">Deskripsi</div>';
            echo '<div class="two tenth small-tablet half-padded align-center skip-two">Jenis</div>';
            echo '</div>';

            $i = 1;
            $arr_tgl = array();
            foreach ($holidays as $tanggal => $deskripsi) {
                $arr_tgl = explode('|||', $tanggal);
                $arr_des = explode('|||', $deskripsi);
                echo '<div class="equalize row">';
                //echo '<div class="one eighth half-padded align-center">'.$i++.'</div>';
                echo '<div class="two tenth small-tablet half-padded align-center">'.$arr_tgl[0].'</div>';
                echo '<div class="five tenth small-tablet half-padded align-center-mobile align-center-small-tablet">'.$arr_des[0].'</div>';
                echo '<div class="two tenth small-tablet half-padded align-center">'.$arr_des[1].'</div>';
                echo '<div class="one tenth small-tablet half-padded align-center"><a style="text-decoration: none;" href="#" onclick="edit_clicked(\''.$arr_tgl[1].'\')"><i class=" icon-edit"></i></a>&nbsp; &nbsp;<a style="text-decoration: none;" href="#" onclick="delete_clicked(\''.$arr_tgl[1].'\',\''.$arr_des[0].'\')"><i class="icon-trash"></i></a></div>';
                //'.site_url('ajax_attendance/delete_holidays/'.$arr_tgl[1]).'
                echo '</div>';
            }

            echo '<div class="equalize row seven eighth"></div>';

            echo '<div/>';
        }
    }
    
    public function delete_holidays($tahun,$bulan,$tgl) {
        //echo "TRUE".$tahun.$bulan.$tgl;
        if (!$this->flexi_auth->is_privileged('del_holidays')) {
            //$this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            echo "You do not have enough privileges";
            exit();            
        }
        
        $this->load->model('Attendance_model');
        $result = $this->Attendance_model->delete_holidays($tahun,$bulan,$tgl);
        //$result = FALSE;
        echo $result;
    }
    
    public function save_new_holidays() {
        if (!$this->flexi_auth->is_privileged('ins_holidays')) {
            //$this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            echo "You do not have enough privileges";
            exit();            
        }
        
        $strtgl = $this->input->post('strtng',TRUE);
        $deskripsi = $this->input->post('strdsk',TRUE);
        $opt = $this->input->post('ijns',TRUE);
        
        $arr_temp = explode(',', $strtgl);
        $str_tgl = $arr_temp[1];
        $arr_tgl = explode('/', $str_tgl);
        $tahun = $arr_tgl[2];
        $bulan = intval($arr_tgl[1]);
        $tanggal = intval($arr_tgl[0]);
        
        $this->load->model('Attendance_model');
        $result = $this->Attendance_model->insert_holidays($deskripsi,$tahun,$bulan,$tanggal,$opt);
        echo $result;
        //print_r($this->input->post());
    }
    
    public function new_holidays() {
        if (!$this->flexi_auth->is_privileged('ins_holidays')) {
            //$this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            echo "You do not have enough privileges";
            exit();            
        }
        
        $this->load->helper('custom_date');
        $arr_month = get_all_month_name();
        $month_array = '[';
        foreach ($arr_month as $m) {
            $month_array = $month_array.'\''.$m.'\',';
        }
        $month_array = substr($month_array, 0, -1)."]";
        
        $select_jenis_libur = '<select name="ajx_new_holidays_jenislibur" id="ajx_new_holidays_jenislibur">';
        $this->load->model('Attendance_model');
        $opt_libur = $this->Attendance_model->get_holidays_type();
        foreach ($opt_libur as $id => $deskripsi) {
            $select_jenis_libur = $select_jenis_libur.'<option value="'.$id.'">'.$deskripsi.'</option>';
        }
        $select_jenis_libur = $select_jenis_libur.'</select>';
        $str_hol_list = $this->Attendance_model->get_holidays_list();
        
        echo '<div class="row">
                <div class="one whole half-padded"><h2>Tambah Hari Libur</h2></div>
              </div>
              <div class="row">
                <div class="two fifth align-center pad-left" id="datepickercontainer" style="height: 270px; width: 260px;"></div>
                <div class="three fifth">
                  <label for="tanggal">Tanggal</label>
                  <span class="select-wrap">
                  <input id="datepicker" type="text" name="ajx_new_holidays_tanggal">
                  </span>
                  <label for="jenis" class="pad-top">Jenis Libur</label>
                  <span class="select-wrap">'.$select_jenis_libur.'</span>
                  <!-- DatePicker -->
                  <script type="text/javascript" src="'.base_url('assets/zebra_datepicker/javascript').'/zebra_datepicker.js"></script>
                  <link rel="stylesheet" href="'.base_url('assets/zebra_datepicker/css').'/default.css" type="text/css">
                  <script>
                    $(document).ready(function() {
                        // assuming the controls you want to attach the plugin to 
                        // have the "datepicker" class set
                        $(\'#datepicker\').Zebra_DatePicker({
                            view: \'years\',
                            months: '.$month_array.',
                            readonly_element: true,
                            show_clear_date: false,
                            format: \'D, d/m/Y\',
                            always_visible: $(\'#datepickercontainer\'),
                            onChange: function(view, elements) {
                                if (view == \'days\') {
                                    elements.each(function() {
                                        if ($(this).data(\'date\').match(/^('.$str_hol_list.')$/)) {
                                            $(this).css({
                                                backgroundColor:\'#C40000\',
                                                color:\'#FFF\'
                                            });
                                            $(this).click(function(){
                                                return false;
                                            });
                                        }    
                                    });
                                }
                           }
                        });
                    });
                    $(\'#savenewholidays\').click(function(){
                        if (($(\'#datepicker\').val().length > 0) && ($(\'#ajx_new_holidays_deskripsi\').val().length > 0)) {
                            formData = {strtng:$(\'#datepicker\').val(),strdsk:$(\'#ajx_new_holidays_deskripsi\').val(),ijns:$(\'#ajx_new_holidays_jenislibur\').val()};
                            $.ajax({
                                url: "'.site_url('ajax_attendance/save_new_holidays/').' ",
                                type: "POST",
                                data : formData,
                                success: function(data){
                                    if (data == 1) {
                                        alert(\'"\'.concat($(\'#ajx_new_holidays_deskripsi\').val().concat(\'" berhasil disimpan\')));
                                        $.magnificPopup.close();
                                        var a = $(\'#datepicker\').val().split(\'/\');
                                        repopulate(a[2].concat(\'/0/0\'));
                                    } else {
                                        alert(data);
                                    }
                                }
                            });
                        } else {
                            alert(\'Harap form diisi dengan benar\');
                        }
                    });
                  </script>
                  <label for="deskripsi" class="pad-top">Deskripsi</label>
                  <span class="select-wrap">
                  <textarea name="ajx_new_holidays_deskripsi" id="ajx_new_holidays_deskripsi" maxlength=100 style="height: 80px; resize: none;"></textarea>
                  </span>
                  <a role="button" class="gap-top gap-bottom" href="#" id="savenewholidays">Simpan</a>
                </div>
              </div>';
    }
    
    public function save_edited_holidays() {
        if (!$this->flexi_auth->is_privileged('ins_holidays')) {
            //$this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            echo "You do not have enough privileges";
            exit();            
        }
        
        $strtgl = $this->input->post('strtng',TRUE);
        $deskripsi = $this->input->post('strdsk',TRUE);
        $opt = $this->input->post('ijns',TRUE);
        $strtbf = $this->input->post('strtbf',TRUE);
        
        $arr_temp = explode(',', $strtgl);
        $str_tgl = trim($arr_temp[1]);
        $arr_tgl = explode('/', $str_tgl);
        $tahun = $arr_tgl[2];
        $bulan = intval($arr_tgl[1]);
        $tanggal = intval($arr_tgl[0]);
        
        $arr_temp2 = explode(',', $strtbf);
        $str_tgl2 = trim($arr_temp2[1]);
        $arr_tgl2 = explode('/', $str_tgl2);
        $tahun2 = $arr_tgl2[2];
        $bulan2 = intval($arr_tgl2[1]);
        $tanggal2 = intval($arr_tgl2[0]);
        
        $this->load->model('Attendance_model');
        $result = $this->Attendance_model->update_holidays($deskripsi,$tahun,$bulan,$tanggal,$opt,$tahun2,$bulan2,$tanggal2);
        echo $result;
        //print_r($this->input->post());
    }
    
    public function edit_holidays($tahun,$bulan,$tgl) {
        if (!$this->flexi_auth->is_privileged('ins_holidays')) {
            //$this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            echo "You do not have enough privileges";
            exit();            
        }
        
        if (empty($tahun) || empty($bulan) || empty($tgl)) {
            echo "Input tanggal salah.";
            exit();
        }
        
        $this->load->helper('custom_date');
        $arr_month = get_all_month_name();
        $month_array = '[';
        foreach ($arr_month as $m) {
            $month_array = $month_array.'\''.$m.'\',';
        }
        $month_array = substr($month_array, 0, -1)."]";
        
        $select_jenis_libur = '<select name="ajx_upd_holidays_jenislibur" id="ajx_upd_holidays_jenislibur">';
        $this->load->model('Attendance_model');
        
        $str_detail = $this->Attendance_model->get_holidays_detail($tahun,$bulan,$tgl);
        $arr_detail = explode('|||', $str_detail);
        
        $opt_libur = $this->Attendance_model->get_holidays_type();
        foreach ($opt_libur as $id => $deskripsi) {
            if ($id == $arr_detail[2]) {
                $selected = ' SELECTED=SELECTED';
            } else {
                $selected = '';
            }
            $select_jenis_libur = $select_jenis_libur.'<option value="'.$id.'"'.$selected.'>'.$deskripsi.'</option>';
        }
        $select_jenis_libur = $select_jenis_libur.'</select>';
        $str_hol_list = $this->Attendance_model->get_holidays_list($tahun,$bulan,$tgl);
        
        echo '<div class="row">
                <div class="one whole half-padded"><h2>Edit Hari Libur</h2></div>
              </div>
              <div class="row">
                <div class="two fifth align-center pad-left" id="datepickercontainer" style="height: 270px; width: 260px;"></div>
                <div class="three fifth">
                  <label for="tanggal">Tanggal</label>
                  <span class="select-wrap">
                  <input id="ajx_bef_upd_holidays_tanggal" type="hidden" name="ajx_bef_upd_holidays_tanggal" value="'.$arr_detail[0].'">
                  <input id="datepicker" type="text" name="ajx_upd_holidays_tanggal" value="'.$arr_detail[0].'">
                  </span>
                  <label for="jenis" class="pad-top">Jenis Libur</label>
                  <span class="select-wrap">'.$select_jenis_libur.'</span>
                  <!-- DatePicker -->
                  <script type="text/javascript" src="'.base_url('assets/zebra_datepicker/javascript').'/zebra_datepicker.js"></script>
                  <link rel="stylesheet" href="'.base_url('assets/zebra_datepicker/css').'/default.css" type="text/css">
                  <script>
                    $(document).ready(function() {
                        // assuming the controls you want to attach the plugin to 
                        // have the "datepicker" class set
                        $(\'#datepicker\').Zebra_DatePicker({
                            view: \'days\',
                            start_date: \''.$arr_detail[0].'\',
                            months: '.$month_array.',
                            readonly_element: true,
                            show_clear_date: false,
                            format: \'D, d/m/Y\',
                            always_visible: $(\'#datepickercontainer\'),
                            onChange: function(view, elements) {
                                if (view == \'days\') {
                                    elements.each(function() {
                                        if ($(this).data(\'date\').match(/^('.$str_hol_list.')$/)) {
                                            $(this).css({
                                                backgroundColor:\'#C40000\',
                                                color:\'#FFF\'
                                            });
                                            $(this).click(function(){
                                                return false;
                                            });
                                        }    
                                    });
                                }
                           }
                        });
                    });
                    $(\'#saveupdholidays\').click(function(){
                        if (($(\'#datepicker\').val().length > 0) && ($(\'#ajx_upd_holidays_deskripsi\').val().length > 0)) {
                            formData = {strtng:$(\'#datepicker\').val(),strdsk:$(\'#ajx_upd_holidays_deskripsi\').val(),ijns:$(\'#ajx_upd_holidays_jenislibur\').val(),strtbf:$(\'#ajx_bef_upd_holidays_tanggal\').val()};
                            $.ajax({
                                url: "'.site_url('ajax_attendance/save_edited_holidays/').' ",
                                type: "POST",
                                data : formData,
                                success: function(data){
                                    if (data == 1) {
                                        var b = $(\'#ajx_bef_upd_holidays_tanggal\').val();
                                        alert(\'"\'.concat($(\'#ajx_upd_holidays_deskripsi\').val().concat(\'" berhasil disimpan\')));
                                        $.magnificPopup.close();
                                        var a = b.split(\'/\');
                                        repopulate(a[2].concat(\'/0/0\'));
                                    } else {
                                        alert(data);
                                    }
                                }
                            });
                        } else {
                            alert(\'Harap form diisi dengan benar\');
                        }
                    });
                  </script>
                  <label for="deskripsi" class="pad-top">Deskripsi</label>
                  <span class="select-wrap">
                  <textarea name="ajx_upd_holidays_deskripsi" id="ajx_upd_holidays_deskripsi" maxlength=100 style="height: 80px; resize: none;">'.$arr_detail[1].'</textarea>
                  </span>
                  <a role="button" class="gap-top gap-bottom" href="#" id="saveupdholidays">Simpan</a>
                </div>
              </div>';
    }
}