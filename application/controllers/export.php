<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export extends CI_Controller {

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
        
    }
    
    public function trim_filename($filename = NULL) {
        $filename = str_replace('&', '', $filename);
        $filename = str_replace(',', '', $filename);
        
        $filename = str_replace(' ', '', $filename);
        return $filename;
    }
    
    public function xls_inc($inCell = 'A1', $mode = 'R', $numInc = 1) {
        $cellColRow = preg_split('/(?<=[A-Z])(?=[0-9]+)/', $inCell);
        $cellCol = $cellColRow[0];
        $cellRow = $cellColRow[1];

        $i = 1;
        while ($i <= $numInc) {
            if ($mode == 'R') {
                $cellRow++;
            } else {
                $cellCol++;
            }
            $i++;
        }

        return $cellCol . $cellRow;
    }
    
    public function xls1($personnel = NULL, $year = NULL, $month = NULL) {
        $this->xls_rpt_attendance_personnel_monthly($personnel, $year, $month);
    }
    
    public function xls_rpt_attendance_personnel_monthly($personnel = NULL, $year = NULL, $month = NULL) {
        if (!$this->flexi_auth->is_privileged('vw_mnth_prsn_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $url_redirect = 'attendance/reporta';
        if (empty($personnel) || empty($year) || empty($month)) {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect($url_redirect);
        }
        
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $this->load->library('Excel');
                
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
                ->setLastModifiedBy("ICT")
                ->setTitle("Laporan Presensi Per Bulan Per Karyawan/Dosen");
                //->setCategory("Report");

        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );
        
        $this->load->helper('custom_string');
        $this->load->model('Personnel_model');
        $personnel_name = do_ucwords($this->Personnel_model->get_personnel_name($personnel));
        
        $this->load->model('Department_model');
        $department_name = do_ucwords($this->Department_model->get_department_name($this->Personnel_model->get_dept_id($personnel)));
        
        $this->load->helper('custom_date');
        $month_year = get_month_name($month).' '.$year;
        
        $sheetNow = 0;
        
        //HEADER VALUE
        $objPHPExcel->setActiveSheetIndex($sheetNow)
            ->setCellValue('A1', 'Laporan Presensi Karyawan/Dosen')
            ->setCellValue('A2', 'Nama Karyawan/Dosen')
            ->setCellValue('D2', ': ' . $personnel_name)
            ->setCellValue('A3', 'Bagian/Prodi')
            ->setCellValue('D3', ': ' . $department_name)
            ->setCellValue('A4', 'Bulan')
            ->setCellValue('D4', ': ' . $month_year);
        //HEADER CELL
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');
        $objPHPExcel->getActiveSheet()->mergeCells('A4:C4');
        $objPHPExcel->getActiveSheet()->mergeCells('D2:F2');
        $objPHPExcel->getActiveSheet()->mergeCells('D3:F3');
        $objPHPExcel->getActiveSheet()->mergeCells('D4:F4');
        //HEADER STYLE
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2:D4')->getFont()->setBold(true);
        
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);

        
        //TABLE HEADER VALUE
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A6', 'Tanggal')
            ->setCellValue('B6', 'Hari')
            ->setCellValue('C6', 'Jam Masuk')
            ->setCellValue('D6', 'Jam Keluar')
            ->setCellValue('E6', 'Durasi Keterlambatan')
            ->setCellValue('F6', 'Keterangan');
        //TABLE HEADER STYLE
        $objPHPExcel->getActiveSheet()->getStyle('E6')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($styleThinBlackBorderOutline);
        
        $objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(30);
        
        $cell = 'A7'; //INITIAL CELL
        
        $this->load->model('Attendance_model');
        $attendance = $this->Attendance_model->get_attendance_data_personnel_monthly($personnel,$year,$month);
        
        foreach ($attendance as $a) {
            if ($a->is_holiday) {
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 5))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 5))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 5))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
            }
            if ($a->is_late) {
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 4))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
            if ($a->is_early) {
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
            $col1 = $a->tanggal;
            $col2 = $a->hari;
            $col3 = $a->jam_masuk;
            $col4 = $a->jam_keluar;
            $col5 = $a->waktu_telat_masuk;
            $col6 = $a->keterangan;
            if ($col6 == '') {
                $col6 = $a->desc_holiday;
            } else if (!($a->desc_holiday == '')) {
                $col6 = $col6."\n".$a->desc_holiday;
            }
            //TABLE CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, $col1)                    
                ->setCellValue($this->xls_inc($cell, 'C', 1), $col2)   
                ->setCellValue($this->xls_inc($cell, 'C', 2), $col3)   
                ->setCellValue($this->xls_inc($cell, 'C', 3), $col4)   
                ->setCellValue($this->xls_inc($cell, 'C', 4), $col5)   
                ->setCellValue($this->xls_inc($cell, 'C', 5), $col6);
            //TABLE CONTENT STYLE
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            
            $cell = $this->xls_inc($cell, 'R', 1);
        }
        
        $sa = $this->Attendance_model->get_summary_attendance_data_personnel_monthly($personnel,$year,$month);
        
        $col1 = $sa->sum_waktu_telat_masuk;
        $col2 = $sa->sum_is_late;
        $col3 = $sa->sum_counter_hadir;
        //SUMMARY TABLE CONTENT VALUE
        $objPHPExcel->getActiveSheet()
            ->setCellValue($this->xls_inc($cell, 'C', 3), 'Total Durasi Keterlambatan')   
            ->setCellValue($this->xls_inc($cell, 'C', 5), $col1);
        //SUMMARY TABLE CONTENT CELL
        $objPHPExcel->getActiveSheet()->mergeCells($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4));
        //SUMMARY TABLE CONTENT STYLE
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getFont()->setBold(true);
        $cell = $this->xls_inc($cell, 'R', 1);
        //SUMMARY TABLE CONTENT VALUE
        $objPHPExcel->getActiveSheet()
            ->setCellValue($this->xls_inc($cell, 'C', 3), 'Total Keterlambatan (hari)')   
            ->setCellValue($this->xls_inc($cell, 'C', 5), $col2);
        //SUMMARY TABLE CONTENT CELL
        $objPHPExcel->getActiveSheet()->mergeCells($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4));
        //SUMMARY TABLE CONTENT STYLE
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getFont()->setBold(true);
        $cell = $this->xls_inc($cell, 'R', 1);
        //SUMMARY TABLE CONTENT VALUE
        $objPHPExcel->getActiveSheet()
            ->setCellValue($this->xls_inc($cell, 'C', 3), 'Total Kehadiran (hari)')   
            ->setCellValue($this->xls_inc($cell, 'C', 5), $col3);
        //SUMMARY TABLE CONTENT CELL
        $objPHPExcel->getActiveSheet()->mergeCells($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4));
        //SUMMARY TABLE CONTENT STYLE
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getFont()->setBold(true);
        $cell = $this->xls_inc($cell, 'R', 1);
        
        $cell = $this->xls_inc($cell, 'R', 1);
        
        //SUMMARY HEADER VALUE
        $objPHPExcel->setActiveSheetIndex($sheetNow)
            ->setCellValue($cell, 'JUMLAH KETERANGAN');
        //SUMMARY HEADER CELL
        $objPHPExcel->getActiveSheet()->mergeCells($cell.':'.$this->xls_inc($cell, 'C', 3));
        //SUMMARY HEADER STYLE
        $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
        
        $summary_of_keterangan = $this->Attendance_model->get_summary_of_keterangan($personnel,$year,$month);
        
        $cell = $this->xls_inc($cell, 'R', 1);
        
        foreach ($summary_of_keterangan as $s) {
            $col1 = $s->keterangan;
            $col2 = $s->jumlah;
            //SUMMARY CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, $col1)                    
                ->setCellValue($this->xls_inc($cell, 'C', 3), $col2);
            //SUMMARY CONTENT CELL
            $objPHPExcel->getActiveSheet()->mergeCells($cell.':'.$this->xls_inc($cell, 'C', 2));
            //SUMMARY CONTENT STYLE
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
        
            $cell = $this->xls_inc($cell, 'R', 1);
        }
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        
        // Rename worksheet
        $arr_temp = explode(' ',$personnel_name);
        $first_name = $arr_temp[0];
        if (sizeof($arr_temp) > 1) {
            $second_name = $arr_temp[1];
        }
        $shortname = $first_name.$second_name;
        $objPHPExcel->getActiveSheet()->setTitle(($sheetNow+1).'.'.$shortname);
        // Page Setup
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(0);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        //clean the output buffer
        ob_end_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        //MONTHLY PERSONNEL ATTENDANCE REPORT
        header('Content-Disposition: attachment;filename="MPAR'.$personnel.'_'.$shortname.'_'.$year.'_'.$month.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    public function xls_rpt_attendance_prsn_mnth_in_dept($dept_id = NULL, $year = NULL, $month = NULL) {
        //AMRNOTE: AJAX RESPONSE
        if (!$this->flexi_auth->is_privileged('vw_mnth_prsn_rpt_all')) {
            echo '<p class="message dismissible error">You do not have enough privileges.</p>';
            exit();
        }
        
        $url_redirect = 'attendance/reporta';
        if (empty($dept_id) || empty($year) || empty($month)) {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect($url_redirect);
        }
        
        $this->load->model('Personnel_model');
        $count_personnel = sizeof($this->Personnel_model->get_all_personnel_name_by_dept_id($dept_id));
        
        if ($count_personnel <= 0) {
            exit();
        }
        
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $this->load->library('Excel');
                
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
                ->setLastModifiedBy("ICT")
                ->setTitle("Laporan Presensi Per Bulan Per Karyawan/Dosen");
                //->setCategory("Report");

        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );
        
        $sheetNow = 0;
        
        $this->load->model('Personnel_model');
        $arr_prsn = $this->Personnel_model->get_all_personnel_name_by_dept_id($dept_id);
        
        foreach ($arr_prsn as $personnel => $prsn_name) {
            $this->load->model('Attendance_model');
            $attendance = $this->Attendance_model->get_attendance_data_personnel_monthly($personnel,$year,$month,TRUE);
            
            /*if ($attendance == NULL) {
                continue;
            }*/
        
            if ($sheetNow > 0) {
                $objPHPExcel->createSheet();
            }
        
            $this->load->helper('custom_string');
            //$personnel_name = do_ucwords($this->Personnel_model->get_personnel_name($personnel));
            $personnel_name = do_ucwords($prsn_name);
        
            $this->load->model('Department_model');
            $department_name = do_ucwords($this->Department_model->get_department_name($this->Personnel_model->get_dept_id($personnel)));
        
            $this->load->helper('custom_date');
            $month_year = get_month_name($month).' '.$year;
        
            //$sheetNow = 0;
        
            //HEADER VALUE
            $objPHPExcel->setActiveSheetIndex($sheetNow)
                ->setCellValue('A1', 'Laporan Presensi Karyawan/Dosen')
                ->setCellValue('A2', 'Nama Karyawan/Dosen')
                ->setCellValue('D2', ': ' . $personnel_name)
                ->setCellValue('A3', 'Bagian/Prodi')
                ->setCellValue('D3', ': ' . $department_name)
                ->setCellValue('A4', 'Bulan')
                ->setCellValue('D4', ': ' . $month_year);
            //HEADER CELL
            $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
            $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:C4');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:F2');
            $objPHPExcel->getActiveSheet()->mergeCells('D3:F3');
            $objPHPExcel->getActiveSheet()->mergeCells('D4:F4');
            //HEADER STYLE
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $objPHPExcel->getActiveSheet()->getStyle('A1:A4')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('D2:D4')->getFont()->setBold(true);
        
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);

        
            //TABLE HEADER VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A6', 'Tanggal')
                ->setCellValue('B6', 'Hari')
                ->setCellValue('C6', 'Jam Masuk')
                ->setCellValue('D6', 'Jam Keluar')
                ->setCellValue('E6', 'Durasi Keterlambatan')
                ->setCellValue('F6', 'Keterangan');
            //TABLE HEADER STYLE
            $objPHPExcel->getActiveSheet()->getStyle('E6')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($styleThinBlackBorderOutline);
        
            $objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(30);
        
            $cell = 'A7'; //INITIAL CELL
            
            foreach ($attendance as $a) {
                if ($a->is_holiday) {
                    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 5))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 5))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 5))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                }
                if ($a->is_late) {
                    $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                    $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 4))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                }
                if ($a->is_early) {
                    $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                }
                $col1 = $a->tanggal;
                $col2 = $a->hari;
                $col3 = $a->jam_masuk;
                $col4 = $a->jam_keluar;
                $col5 = $a->waktu_telat_masuk;
                $col6 = $a->keterangan;
                if ($col6 == '') {
                    $col6 = $a->desc_holiday;
                } else if (!($a->desc_holiday == '')) {
                    $col6 = $col6."\n".$a->desc_holiday;
                }
                //TABLE CONTENT VALUE
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($cell, $col1)                    
                    ->setCellValue($this->xls_inc($cell, 'C', 1), $col2)   
                    ->setCellValue($this->xls_inc($cell, 'C', 2), $col3)   
                    ->setCellValue($this->xls_inc($cell, 'C', 3), $col4)   
                    ->setCellValue($this->xls_inc($cell, 'C', 4), $col5)   
                    ->setCellValue($this->xls_inc($cell, 'C', 5), $col6);
                //TABLE CONTENT STYLE
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $cell = $this->xls_inc($cell, 'R', 1);
            }
        
            $sa = $this->Attendance_model->get_summary_attendance_data_personnel_monthly($personnel,$year,$month);
            
            $col1 = empty($sa)?'':$sa->sum_waktu_telat_masuk;
            $col2 = empty($sa)?'':$sa->sum_is_late;
            $col3 = empty($sa)?'':$sa->sum_counter_hadir;
            //SUMMARY TABLE CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($this->xls_inc($cell, 'C', 3), 'Total Durasi Keterlambatan')   
                ->setCellValue($this->xls_inc($cell, 'C', 5), $col1);
            //SUMMARY TABLE CONTENT CELL
            $objPHPExcel->getActiveSheet()->mergeCells($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4));
            //SUMMARY TABLE CONTENT STYLE
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getFont()->setBold(true);
            $cell = $this->xls_inc($cell, 'R', 1);
            //SUMMARY TABLE CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($this->xls_inc($cell, 'C', 3), 'Total Keterlambatan (hari)')   
                ->setCellValue($this->xls_inc($cell, 'C', 5), $col2);
            //SUMMARY TABLE CONTENT CELL
            $objPHPExcel->getActiveSheet()->mergeCells($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4));
            //SUMMARY TABLE CONTENT STYLE
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getFont()->setBold(true);
            $cell = $this->xls_inc($cell, 'R', 1);
            //SUMMARY TABLE CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($this->xls_inc($cell, 'C', 3), 'Total Kehadiran (hari)')   
                ->setCellValue($this->xls_inc($cell, 'C', 5), $col3);
            //SUMMARY TABLE CONTENT CELL
            $objPHPExcel->getActiveSheet()->mergeCells($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4));
            //SUMMARY TABLE CONTENT STYLE
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3).':'.$this->xls_inc($cell, 'C', 5))->getFont()->setBold(true);
            $cell = $this->xls_inc($cell, 'R', 1);

            $cell = $this->xls_inc($cell, 'R', 1);

            //SUMMARY HEADER VALUE
            $objPHPExcel->setActiveSheetIndex($sheetNow)
                ->setCellValue($cell, 'JUMLAH KETERANGAN');
            //SUMMARY HEADER CELL
            $objPHPExcel->getActiveSheet()->mergeCells($cell.':'.$this->xls_inc($cell, 'C', 3));
            //SUMMARY HEADER STYLE
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);

            $summary_of_keterangan = $this->Attendance_model->get_summary_of_keterangan($personnel,$year,$month);
            
            if ($summary_of_keterangan == NULL) {
                $summary_of_keterangan = $this->Attendance_model->get_summary_of_keterangan('NOTUSE','NOTUSE','NOTUSE',TRUE);
            }
            
            $cell = $this->xls_inc($cell, 'R', 1);

            foreach ($summary_of_keterangan as $s) {
                $col1 = $s->keterangan;
                $col2 = $s->jumlah;
                //SUMMARY CONTENT VALUE
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($cell, $col1)                    
                    ->setCellValue($this->xls_inc($cell, 'C', 3), $col2);
                //SUMMARY CONTENT CELL
                $objPHPExcel->getActiveSheet()->mergeCells($cell.':'.$this->xls_inc($cell, 'C', 2));
                //SUMMARY CONTENT STYLE
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);

                $cell = $this->xls_inc($cell, 'R', 1);
            }

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

            // Rename worksheet
            $first_name = '';
            $arr_temp = explode(' ',$personnel_name);
            $first_name = $arr_temp[0];
            $second_name = '';
            if (sizeof($arr_temp) > 1) {
                $second_name = $arr_temp[1];
            }
            $shortname = $first_name.$second_name;
            $objPHPExcel->getActiveSheet()->setTitle(($sheetNow+1).'.'.$shortname);
            
            //Page Setup
            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(0);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
            
            $sheetNow++;
        }
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        //clean the output buffer
        /*ob_end_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        //MONTHLY PERSONNEL ATTENDANCE REPORT
        header('Content-Disposition: attachment;filename="MPAR'.$personnel.'_'.$shortname.'_'.$year.'_'.$month.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');*/
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save($this->Parameter->get_value('FOLDER_ON_SERVER_FOR_MPAR').'/MPAR'.$dept_id.'_'.$this->trim_filename($department_name).'_'.$year.'_'.$month.'.xls');
    }
    
    public function xls_rpt_attendance_department_yearly($dept_id = NULL, $year = NULL, $direct_download = 1) {
        //AMRNOTE: AJAX RESPONSE
        if ((!$this->flexi_auth->is_privileged('vw_year_dept_rpt_all')) && ($direct_download == 0)) {
            echo '<p class="message dismissible error">You do not have enough privileges.</p>';
            exit();
        } else if (!$this->flexi_auth->is_privileged('vw_year_dept_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $url_redirect = 'attendance/reportb';
        if (empty($dept_id) || empty($year)) {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect($url_redirect);
        }
        
        $this->load->model('Personnel_model');
        $count_personnel = sizeof($this->Personnel_model->get_all_personnel_name_by_dept_id($dept_id));
        
        if ($count_personnel <= 0) {
            if ($direct_download) {
                $this->session->set_flashdata('message', 'Unable to find personnel data.');
                $this->session->set_flashdata('message_type', 'warning');
                redirect($url_redirect);
            } else {
                exit();
            }
        }
        
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $this->load->library('Excel');
                
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
                ->setLastModifiedBy("ICT")
                ->setTitle("Laporan Presensi Per Tahun Per Bagian/Prodi");

        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );
        
        $styleCustom = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );
            
        $this->load->helper('custom_string');
        $this->load->model('Department_model');
        $department_name = do_ucwords($this->Department_model->get_department_name($dept_id));
        
        $this->load->helper('custom_date');
        $all_month = get_all_month_name();
        
        $sheetNow = 0;
        
        $this->load->model('Personnel_model');
        $arr_prsn = $this->Personnel_model->get_all_personnel_name_by_dept_id($dept_id);
        
        /*var_dump($arr_prsn);
        exit;*/
        
        $this->load->model('Attendance_model');
        $ske = $this->Attendance_model->get_summary_of_keterangan_with_group('NOTUSE','NOTUSE','NOTUSE',TRUE);
        $dflt_col4 = '';
        foreach ($ske as $ske) {
            $dflt_col4 = $dflt_col4 . $ske->keterangan . " : " . $ske->jumlah . "\n";
        }
        $dflt_col4 = substr($dflt_col4, 0, (strlen($dflt_col4) - 1));
        
        $jml_prsn_in_sheet = 0;
        $max_prsn_in_sheet = 7;
        
        $num_col = 4;
        
        $cell_col_first = 'B5';
        
        foreach ($arr_prsn as $prsn_id => $prsn_name) {
            if (($sheetNow > 0) && ($jml_prsn_in_sheet == 0)) {
                $objPHPExcel->createSheet();
            }
            $jml_prsn_in_sheet++;
            if ($jml_prsn_in_sheet == 1) {
                //HEADER VALUE
                $objPHPExcel->setActiveSheetIndex($sheetNow)
                    ->setCellValue('A1', 'Laporan Presensi Per Tahun Per Bagian/Prodi')
                    ->setCellValue('A2', 'Bagian/Prodi')
                    ->setCellValue('C2', ': ' . $department_name)
                    ->setCellValue('A3', 'Tahun')
                    ->setCellValue('C3', ': ' . $year);

                //HEADER CELL
                $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
                $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
                $objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
                $objPHPExcel->getActiveSheet()->mergeCells('C2:E2');
                $objPHPExcel->getActiveSheet()->mergeCells('C3:E3');
                //HEADER STYLE
                $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('C2:C3')->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);

                $objPHPExcel->getActiveSheet()->setCellValue('A5', 'BULAN');
                $objPHPExcel->getActiveSheet()->mergeCells('A5:A7');
                $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $cell = 'A8';
                for ($inc_month = 1; $inc_month <= 12; $inc_month++) {
                    $month_name = $all_month[$inc_month];
                    $objPHPExcel->getActiveSheet()->setCellValue($cell, substr($month_name,0,3));
                    $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
                    $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle($cell)->getFont()->setBold(true);
                    $cell = $this->xls_inc($cell, 'R', 1);
                }
                
                $objPHPExcel->getActiveSheet()->setCellValue($cell, 'TOTAL');
                $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($cell)->getFont()->setBold(true);
                
                $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleCustom);
                
                //$cell_col_first = 'B5';
                $cell_col = $this->xls_inc($cell_col_first,'R',1);
                $cell = 'B6'; //INITIAL CELL
            }
            
            //COL PERSONNEL NAME
            $this->load->helper('custom_string');
            $objPHPExcel->getActiveSheet()->setCellValue($cell, do_ucwords($prsn_name));
            //COL PERSONNEL NAME STYLE
            $objPHPExcel->getActiveSheet()->mergeCells($cell.':'.$this->xls_inc($cell, 'C', 3));
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
            //COL TITLE
            $cell = $this->xls_inc($cell, 'R', 1);
            $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, 'Hadir')
                ->setCellValue($this->xls_inc($cell, 'C', 1), 'Terlambat')
                ->setCellValue($this->xls_inc($cell, 'C', 2), 'Durasi Terlambat')
                ->setCellValue($this->xls_inc($cell, 'C', 3), 'Keterangan');
            //COL STYLE
            $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getRowDimension(substr($cell, -1))->setRowHeight(30);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->getAlignment()->setWrapText(true);
            
            //20131206
            //ROW TOTAL
            $total_person_sum_counter_hadir = 0;
            $total_person_sum_is_late = 0;
            $total_person_sum_detik_telat_masuk = 0;
            
            $arr_person_total_keterangan = array();
            
            for ($inc_month = 1; $inc_month <= 12; $inc_month++) {
                //COL CONTENT
                $cell = $this->xls_inc($cell, 'R', 1);
                //$sa = new stdClass();
                $sa = $this->Attendance_model->get_summary_attendance_data_personnel_monthly($prsn_id,$year,$inc_month);
                
                if ($sa != NULL) {
                    $col1 = $sa->sum_counter_hadir;
                    $col2 = $sa->sum_is_late;
                    $col3 = $sa->sum_waktu_telat_masuk;
                    $col4 = $sa->sum_detik_telat_masuk;
                    //20131206
                    //ROW TOTAL
                    $total_person_sum_counter_hadir = $total_person_sum_counter_hadir+$col1;
                    $total_person_sum_is_late       = $total_person_sum_is_late+$col2;
                    $total_person_sum_detik_telat_masuk = $total_person_sum_detik_telat_masuk+$col4;
                } else {
                    $col1 = 0;
                    $col2 = 0;
                    $col3 = 0;
                }
                
                $sk = $this->Attendance_model->get_summary_of_keterangan_with_group($prsn_id,$year,$inc_month);
                
                $col4 = '';
                    
                if ($sk != NULL) {
                    foreach ($sk as $sk) {
                        $col4 = $col4 . $sk->keterangan . " : " . $sk->jumlah . "\n";
                        //20131206
                        //ROW TOTAL
                        $arr_person_total_keterangan[$sk->keterangan] = (isset($arr_person_total_keterangan[$sk->keterangan])?$arr_person_total_keterangan[$sk->keterangan]:0)+$sk->jumlah;
                    }
                    $col4 = substr($col4, 0, (strlen($col4) - 1));
                } else {
                    $col4 = $dflt_col4;
                }
                
                $objPHPExcel->getActiveSheet()
                    ->setCellValue($cell, $col1)
                    ->setCellValue($this->xls_inc($cell, 'C', 1), $col2)
                    ->setCellValue($this->xls_inc($cell, 'C', 2), $col3)
                    ->setCellValue($this->xls_inc($cell, 'C', 3), $col4);
                
                $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getAlignment()->setWrapText(true);
                
                if ($sk == NULL) {
                    $style_grey = array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb'=>'FAFAFA'),
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->applyFromArray( $style_grey );
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . $this->xls_inc($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                }
            }
            
            //20131206
            //ROW TOTAL
            $total_person_jam = floor($total_person_sum_detik_telat_masuk/3600);
            if ($total_person_jam <= 9) { $total_person_jam = '0'.$total_person_jam; }
            $total_person_menit = ($total_person_sum_detik_telat_masuk%3600)/60;
            if ($total_person_menit <= 9) { $total_person_menit = '0'.$total_person_menit; }
            $cell = $this->xls_inc($cell, 'R');
            
            $str_total_keterangan = '';
            foreach ($arr_person_total_keterangan as $key => $value) {
                $str_total_keterangan = $str_total_keterangan . $key . " : " . $value . "\n";
            }
            $str_total_keterangan = substr($str_total_keterangan, 0, (strlen($str_total_keterangan) - 1));
            
            $objPHPExcel->getActiveSheet()
                    ->setCellValue($cell, $total_person_sum_counter_hadir)
                    ->setCellValue($this->xls_inc($cell, 'C', 1), $total_person_sum_is_late)
                    ->setCellValue($this->xls_inc($cell, 'C', 2), $total_person_jam.':'.$total_person_menit)
                    ->setCellValue($this->xls_inc($cell, 'C', 3), $str_total_keterangan);
            
            $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
            
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->applyFromArray($styleCustom);
            
            $cellColRow = preg_split('/(?<=[A-Z])(?=[0-9]+)/', $cell);
            $cellCol = $cellColRow[0];

            $objPHPExcel->getActiveSheet()->getColumnDimension($cellCol++)->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellCol++)->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellCol++)->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellCol++)->setAutoSize(true);
            
            //NEXT COL PERSONNEL NAME
            $cell = $this->xls_inc($cell_col, 'C', $num_col);
            $cell_col = $cell;
            
            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle(($sheetNow+1).'.'.$this->trim_filename($department_name));
            
            // Page Setup
            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(0);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
            
            if ($max_prsn_in_sheet == $jml_prsn_in_sheet) {
                $objPHPExcel->getActiveSheet()->setCellValue($cell_col_first, 'PRESENSI KARYAWAN');
                $row_range = $cell_col_first.':'.$this->xls_inc($cell_col_first, 'C', ($num_col*$jml_prsn_in_sheet-1));
                $objPHPExcel->getActiveSheet()->mergeCells($row_range);
                $objPHPExcel->getActiveSheet()->getStyle($row_range)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($row_range)->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($row_range)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($row_range)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                //20131205
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                
                $jml_prsn_in_sheet = 0;
                $sheetNow++;
            }
        }
        
        $objPHPExcel->getActiveSheet()->setCellValue($cell_col_first, 'PRESENSI KARYAWAN');
        $row_range = $cell_col_first.':'.$this->xls_inc($cell_col_first, 'C', ($num_col*$jml_prsn_in_sheet-1));
        $objPHPExcel->getActiveSheet()->mergeCells($row_range);
        $objPHPExcel->getActiveSheet()->getStyle($row_range)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($row_range)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($row_range)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($row_range)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        //clean the output buffer
        //ob_end_clean();
        
        if ($direct_download == 0) {
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($this->Parameter->get_value('FOLDER_ON_SERVER_FOR_YDAR').'/YDAR'.$dept_id.'_'.$this->trim_filename($department_name).'_'.$year.'.xls');
        } else {
            // Redirect output to a client’s web browser (Excel5)
            header('Content-Type: application/vnd.ms-excel');
            //ATTENDANCE REPORT
            header('Content-Disposition: attachment;filename="YDAR'.$dept_id.'_'.$this->trim_filename($department_name).'_'.$year.'.xls"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
    }
    
    public function xls3($year = NULL, $month = NULL, $tanggal = NULL, $departments = NULL) {
        $this->xls_rpt_attendance_daily($year, $month, $tanggal, $departments);
    }
    
    public function xls_rpt_attendance_daily($year = NULL, $month = NULL, $tanggal = NULL, $departments = NULL) {
        if (!$this->flexi_auth->is_privileged('vw_daily_rpt')) {
            $this->session->set_flashdata('message', '<p class="error">You do not have enough privileges.</p>');
            redirect('user');
        }
        
        $url_redirect = 'attendance/reportc';
        if (empty($year) || empty($month) || empty($tanggal) || (empty($departments))) {
            $this->session->set_flashdata('message', 'Unable to find attendance data.');
            $this->session->set_flashdata('message_type', 'error');
            redirect($url_redirect);
        }
        
        $arr_temp = explode('D', $departments);
        $arr_dept = array();
        
        foreach ($arr_temp as $value) {
            if (!empty($value)) {
                $arr_dept[$value] = 1;
            }
        }
        
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $this->load->library('Excel');
                
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
                ->setLastModifiedBy("ICT")
                ->setTitle("Laporan Presensi Per Tanggal");

        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );
        
        $this->load->helper('custom_string');
        $sheetNow = 0;
        
        $this->load->helper('custom_date');
        $tgl_desc = $tanggal.' '.get_month_name($month).' '.$year;
        
        //HEADER VALUE
        $objPHPExcel->setActiveSheetIndex($sheetNow)
            ->setCellValue('A1', 'Laporan Presensi Karyawan/Dosen')
            ->setCellValue('A2', 'Tanggal : '.$tgl_desc);
        //HEADER CELL
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
        //HEADER STYLE
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);
        
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);

        
        //TABLE HEADER VALUE
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A4', 'No.')    
            ->setCellValue('B4', 'Nama')
            ->setCellValue('C4', 'Bagian/Prodi')
            ->setCellValue('D4', 'Jam Masuk')
            ->setCellValue('E4', 'Jam Keluar')
            ->setCellValue('F4', 'Durasi Keterlambatan')
            ->setCellValue('G4', 'Keterangan');
        //TABLE HEADER STYLE
        $objPHPExcel->getActiveSheet()->getStyle('F4')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:G4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A4:G4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A4:G4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('D4')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('F4')->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($styleThinBlackBorderOutline);
        
        $objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(30);
        
        $cell = 'A5'; //INITIAL CELL
        
        $this->load->model('Attendance_model');
        $attendance = $this->Attendance_model->get_attendance_data_on_date($year,$month,$tanggal,$arr_dept);
        
        $i = 1;
        foreach ($attendance as $a) {
            if ($a->is_late) {
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
            if ($a->is_early) {
                $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 4))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
            
            $col1 = $i++;
            $col2 = do_ucwords($a->nama);
            $col3 = do_ucwords($a->dept_name);
            $col4 = $a->jam_masuk;
            $col5 = $a->jam_keluar;
            $col6 = $a->waktu_telat_masuk;
            $col7 = $a->keterangan;
            /*if ($col6 == '') {
                $col6 = $a->desc_holiday;
            } else if (!($a->desc_holiday == '')) {
                $col6 = $col6."\n".$a->desc_holiday;
            }*/
            //TABLE CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, $col1)                    
                ->setCellValue($this->xls_inc($cell, 'C', 1), $col2)   
                ->setCellValue($this->xls_inc($cell, 'C', 2), $col3)   
                ->setCellValue($this->xls_inc($cell, 'C', 3), $col4)   
                ->setCellValue($this->xls_inc($cell, 'C', 4), $col5)   
                ->setCellValue($this->xls_inc($cell, 'C', 5), $col6)
                ->setCellValue($this->xls_inc($cell, 'C', 6), $col7);
            //TABLE CONTENT STYLE
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 6))->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 6))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 1) . ":" . $this->xls_inc($cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3) . ":" . $this->xls_inc($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            
            $cell = $this->xls_inc($cell, 'R', 1);
        }
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle(($sheetNow+1).'.'.$year.$month.$tanggal);
        
        // Page Setup
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        //clean the output buffer
        ob_end_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        //MONTHLY PERSONNEL ATTENDANCE REPORT
        header('Content-Disposition: attachment;filename="DAR'.$year.'_'.$month.'_'.$tanggal.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}