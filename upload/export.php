<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export extends CI_Controller {

    public function index() {
        
    }
    
    public function xls1($personnel = NULL, $year = NULL, $month = NULL) {
        $this->xls_rpt_attendance_personnel_monthly($personnel, $year, $month);
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
    
    public function xls_rpt_attendance_personnel_monthly($personnel = NULL, $year = NULL, $month = NULL) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $this->load->library('Excel');
                
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
                //->setLastModifiedBy("System")
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
            //TABLE CONTENT VALUE
            $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, $col1)                    
                ->setCellValue($this->xls_inc($cell, 'C', 1), $col2)   
                ->setCellValue($this->xls_inc($cell, 'C', 2), $col3)   
                ->setCellValue($this->xls_inc($cell, 'C', 3), $col4)   
                ->setCellValue($this->xls_inc($cell, 'C', 4), $col5)   
                ->setCellValue($this->xls_inc($cell, 'C', 5), $col6);
            //TABLE CONTENT STYLE
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
            $objPHPExcel->getActiveSheet()->getStyle($cell.':'.$this->xls_inc($cell, 'C', 3))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($this->xls_inc($cell, 'C', 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

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
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
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
        
        
        /*$sheet_writer = PHPExcel_IOFactory::createWriter($sheet, 'Excel5');
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="att_report_'.date('dMy').'.xls"');
        header('Cache-Control: max-age=0');

        $sheet_writer->save('php://output');*/
    }
}