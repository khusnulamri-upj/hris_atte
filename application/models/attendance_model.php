<?php

class Attendance_model extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_all_year_in_attendance_holidays() {
        $tbl = 'attendance';
        $col_year = 'date';
        $col_year_alias = 'year';
        
        $tbl_holidays = 'libur';
        $col_year_holidays = 'tgl';
        
        $this->load->database('default');
        /*$sql = "SELECT DATE_FORMAT($col_year,'%Y') AS $col_year_alias 
            FROM $tbl 
            GROUP BY DATE_FORMAT($col_year,'%Y')
            ORDER BY $col_year DESC";*/
        $sql = "SELECT * FROM (
            SELECT DATE_FORMAT($col_year,'%Y') AS $col_year_alias 
            FROM $tbl
            UNION
            SELECT DATE_FORMAT($col_year_holidays,'%Y') AS $col_year_alias
            FROM $tbl_holidays
            WHERE $col_year_holidays IS NOT NULL
            ) y
            GROUP BY $col_year_alias
            ORDER BY $col_year_alias DESC";
        $query = $this->db->query($sql);
        $arr_year = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_year[$obj->$col_year_alias] = $obj->$col_year_alias;
            }
        }
        $this->db->close();
        return $arr_year;
    }

    function get_all_year() {
        $tbl = 'attendance';
        $col_year = 'date';
        $col_year_alias = 'year';
        
        $this->load->database('default');
        $sql = "SELECT DATE_FORMAT($col_year,'%Y') AS $col_year_alias 
            FROM $tbl 
            GROUP BY DATE_FORMAT($col_year,'%Y')
            ORDER BY $col_year DESC";
        $query = $this->db->query($sql);
        $arr_year = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_year[$obj->$col_year_alias] = $obj->$col_year_alias;
            }
        }
        $this->db->close();
        return $arr_year;
    }
    
    function get_all_keterangan($arr_init = array()) {
        $tbl = 'opt_keterangan';
        $col_ket_id = 'opt_keterangan_id';
        $col_ket_desc = 'content';
        $col_order_by = 'order_no';
        
        $this->load->database('default');
        $sql = "SELECT $col_ket_id,
            $col_ket_desc
            FROM $tbl
            WHERE expired_time IS NULL
            ORDER BY $col_order_by";
        $query = $this->db->query($sql);
        //$arr_ket = array();
        $arr_ket = $arr_init;
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr_ket[$obj->$col_ket_id] = $obj->$col_ket_desc;
            }
        }
        $this->db->close();
        return $arr_ket;
    }
    
    function is_attendance_data_exist($user_id,$tahun,$bulan) {
        if (empty($user_id) || empty($tahun) || empty($bulan)) {
            return FALSE;
        }
        
        $tbl = 'attendance';
        $col_user_id = 'user_id';
        $col_date = 'date';
        
        $this->load->database('default');
        $sql = "SELECT $col_user_id 
            FROM $tbl
            WHERE $col_date > DATE_ADD(MAKEDATE($tahun, 31), INTERVAL ($bulan-2) MONTH)
            AND $col_date < DATE_ADD(MAKEDATE($tahun, 1), INTERVAL ($bulan) MONTH)
            AND $col_user_id = $user_id";
        $query = $this->db->query($sql);
        $result = FALSE;
        if ($query->num_rows() > 0) {
            $result = TRUE;
        }
        $this->db->close();
        return $result;
    }
    
    function get_attendance_data_personnel_monthly($user_id,$tahun,$bulan,$bypass_for_report = FALSE,$next_month_open_entry = FALSE) {
        if (empty($user_id) || empty($tahun) || empty($bulan)) {
            return NULL;
        }
        
        if (!$this->is_attendance_data_exist($user_id, $tahun, $bulan)) {
            if ((!$bypass_for_report) && (!$next_month_open_entry)) {
                return NULL;
            } else if (($next_month_open_entry) && (($bulan-date('n')) > $this->Parameter->get_value('BULAN_KE_DEPAN'))) {
                return NULL;
            }
        }
        
        /*$fmt_date = '%d/%m/%Y';
        $fmt_time = '%H:%i';
        $late_limit = '07:40';
        $early_limit = '16:30';
        $time_divider = '12:00';*/
        
        $fmt_date = $this->Parameter->get_value('FORMAT_TGL');
        $fmt_time = $this->Parameter->get_value('FORMAT_JAM');
        $late_limit = $this->Parameter->get_value('JAM_MASUK');
        $early_limit = $this->Parameter->get_value('JAM_KELUAR');
        $time_divider = $this->Parameter->get_value('JAM_TENGAH');
        $detik_pertimbangan = $this->Parameter->get_value('DETIK_PERTIMBANGAN');
        
        
        $this->load->database('default');
        
        /*$sql = "SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.is_same,
            att2.opt_keterangan,
            opt.content AS keterangan,
            TIME_FORMAT(SEC_TO_TIME(att2.detik_telat_masuk),'$fmt_time') AS waktu_telat_masuk,
            att2.detik_telat_masuk,
            att2.is_late,
            att2.is_early,
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            if(gen_lbr2.is_holiday,0,if(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(a.max_time = a.min_time,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id";*/
        
        /*$sql = "SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.is_same,
            att2.opt_keterangan,
            opt.content AS keterangan,
            TIME_FORMAT(SEC_TO_TIME(att2.detik_telat_masuk),'$fmt_time') AS waktu_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,NULL,TIME_FORMAT(SEC_TO_TIME(att2.detik_telat_masuk*att2.late_is_ignored),'$fmt_time')) AS waktu_telat_masuk,
            att2.detik_telat_masuk AS detik_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,0,att2.detik_telat_masuk*att2.late_is_ignored) AS detik_telat_masuk,
            att2.is_late AS is_late,
            IF(gen_lbr2.is_holiday,0,att2.is_late*att2.late_is_ignored) AS is_late_actual,
            att2.is_early AS is_early,
            IF(gen_lbr2.is_holiday,0,att2.is_early*att2.late_is_ignored) AS is_early_actual, 
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl,
            IF(IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)<att2.counter_hadir_opt,att2.counter_hadir_opt,IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)) AS counter_hadir,
            att2.late_is_ignored
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      2013 AS tahun,
                      11 AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir_att) AS counter_hadir_att,
              MAX(att.counter_hadir_opt) AS counter_hadir_opt,
              MAX(att.late_is_ignored) AS late_is_ignored
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir_att,
                NULL AS counter_hadir_opt,
                NULL AS late_is_ignored
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(a.max_time = a.min_time,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = 4
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                NULL,
                o.counter_hadir,
                o.late_is_ignored
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = 4
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id";*/
        
        /*$sql = "SELECT att3.*,
          IF(att3.is_late*att3.late_is_counted IS NULL,0,att3.is_late*att3.late_is_counted) AS counter_late,
          IF(att3.detik_telat_masuk_actual*att3.late_is_counted IS NULL,0,att3.detik_telat_masuk_actual*att3.late_is_counted) AS detik_telat_masuk,
          TIME_FORMAT(SEC_TO_TIME(IF(att3.detik_telat_masuk_actual*att3.late_is_counted>0,att3.detik_telat_masuk_actual*att3.late_is_counted,NULL)),'$fmt_time') AS waktu_telat_masuk
        FROM (  
          SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.opt_keterangan,
            opt.content AS keterangan,
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            att2.is_same,
            att2.is_late,
            att2.is_early,
            att2.detik_telat_masuk AS detik_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl,
            IF(IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)) IS NULL, 0, IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att))) AS counter_hadir,
            IF(gen_lbr2.is_holiday,0,IF(att2.late_is_counted IS NULL,IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,0,1)),att2.late_is_counted)) AS late_is_counted
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir_att) AS counter_hadir_att,
              MAX(att.counter_hadir_opt) AS counter_hadir_opt,
              MAX(att.late_is_counted) AS late_is_counted
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir_att,
                NULL AS counter_hadir_opt,
                NULL AS late_is_counted
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(a.max_time = a.min_time,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                NULL,
                o.counter_hadir,
                o.late_is_counted
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id)
            att3";*/
        
        $sql = "SELECT att3.*,
          IF(att3.is_late*att3.late_is_counted IS NULL,0,att3.is_late*att3.late_is_counted) AS counter_late,
          IF(att3.detik_telat_masuk_actual*att3.late_is_counted IS NULL,0,att3.detik_telat_masuk_actual*att3.late_is_counted) AS detik_telat_masuk,
          TIME_FORMAT(SEC_TO_TIME(IF(att3.detik_telat_masuk_actual*att3.late_is_counted>0,att3.detik_telat_masuk_actual*att3.late_is_counted,NULL)),'$fmt_time') AS waktu_telat_masuk
        FROM (  
          SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.opt_keterangan,
            opt.content AS keterangan,
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            att2.is_same,
            att2.is_late,
            att2.is_early,
            att2.detik_telat_masuk AS detik_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl,
            IF(IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)) IS NULL, 0, IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att))) AS counter_hadir,
            IF(gen_lbr2.is_holiday,0,IF(att2.late_is_counted IS NULL,IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,0,1)),att2.late_is_counted)) AS late_is_counted
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir_att) AS counter_hadir_att,
              MAX(att.counter_hadir_opt) AS counter_hadir_opt,
              MAX(att.late_is_counted) AS late_is_counted
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir_att,
                NULL AS counter_hadir_opt,
                NULL AS late_is_counted
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                NULL,
                o.counter_hadir,
                o.late_is_counted
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id)
            att3";
        
        //$query = $this->db->query($sql, array($fmt_date, (integer)$bulan, (integer)$tahun, (integer)$user_id, (integer)$user_id));
        $query = $this->db->query($sql);
        $return = NULL;
        if ($query->num_rows() > 0) {
            $return = $query->result();
        }
        $this->db->close();
        return $return;
    }
    
    function get_summary_attendance_data_personnel_monthly($user_id,$tahun,$bulan) {
        if (empty($user_id) || empty($tahun) || empty($bulan)) {
            return NULL;
        }
        
        if (!$this->is_attendance_data_exist($user_id, $tahun, $bulan)) {
            return NULL;
        }
        
        /*$fmt_date = '%d/%m/%Y';
        $fmt_time = '%H:%i';
        $late_limit = '07:40';
        $early_limit = '16:30';
        $time_divider = '12:00';*/
        
        $fmt_date = $this->Parameter->get_value('FORMAT_TGL');
        $fmt_time = $this->Parameter->get_value('FORMAT_JAM');
        $late_limit = $this->Parameter->get_value('JAM_MASUK');
        $early_limit = $this->Parameter->get_value('JAM_KELUAR');
        $time_divider = $this->Parameter->get_value('JAM_TENGAH');
        $detik_pertimbangan = $this->Parameter->get_value('DETIK_PERTIMBANGAN');
        
        $this->load->database('default');
        
        /*$sql = "SELECT TIME_FORMAT(SEC_TO_TIME(SUM(summ.detik_telat_masuk)),'$fmt_time') AS sum_waktu_telat_masuk, SUM(summ.detik_telat_masuk) AS sum_detik_telat_masuk, SUM(summ.is_late) AS sum_is_late, SUM(summ.counter_hadir) AS sum_counter_hadir FROM (
          SELECT gen_lbr2.tanggal,
            att2.opt_keterangan,
            opt.content AS keterangan,
            if(att2.detik_telat_masuk,att2.detik_telat_masuk,0) AS detik_telat_masuk,
            if(att2.is_late,att2.is_late,0) AS is_late,
            if(att2.is_early,att2.is_early,0) AS is_early,
            if(att2.counter_hadir,att2.counter_hadir,0) AS counter_hadir
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir) AS counter_hadir
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                o.counter_hadir
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id
          ) summ";*/
        
        /*$ttl = "CONCAT(
            IF(
                (SUM(summ.detik_telat_masuk) DIV 3600)>9,
                (SUM(summ.detik_telat_masuk) DIV 3600),
                CONCAT(
                    '0',
                    (SUM(summ.detik_telat_masuk) DIV 3600)
                )
            ),
            '".$this->Parameter->get_value('SEPARATOR_JAM')."',
            IF(
                ((SUM(summ.detik_telat_masuk) MOD 3600) DIV 60)>9,
                ((SUM(summ.detik_telat_masuk) MOD 3600) DIV 60),
                CONCAT(
                    '0',
                    ((SUM(summ.detik_telat_masuk) MOD 3600) DIV 60)
                )
            )
            )";*/
        
        $ttl = "CONCAT(
            IF(
                (SUM(smmry.detik_telat_masuk) DIV 3600)>9,
                (SUM(smmry.detik_telat_masuk) DIV 3600),
                CONCAT(
                    '0',
                    (SUM(smmry.detik_telat_masuk) DIV 3600)
                )
            ),
            '".$this->Parameter->get_value('SEPARATOR_JAM')."',
            IF(
                ((SUM(smmry.detik_telat_masuk) MOD 3600) DIV 60)>9,
                ((SUM(smmry.detik_telat_masuk) MOD 3600) DIV 60),
                CONCAT(
                    '0',
                    ((SUM(smmry.detik_telat_masuk) MOD 3600) DIV 60)
                )
            )
            )";
        
        //$ttl = "CONCAT((SUM(summ.detik_telat_masuk) DIV 3600),'".$this->Parameter->get_value('SEPARATOR_JAM')."',((SUM(summ.detik_telat_masuk) MOD 3600) DIV 60))";
        //$ttl = "SUM(summ.detik_telat_masuk)";
        
        /*$sql = "SELECT $ttl AS sum_waktu_telat_masuk, SUM(summ.detik_telat_masuk) AS sum_detik_telat_masuk, SUM(summ.is_late) AS sum_is_late, SUM(summ.counter_hadir) AS sum_counter_hadir FROM (
          SELECT gen_lbr2.tanggal,
            att2.opt_keterangan,
            opt.content AS keterangan,
            if(att2.detik_telat_masuk,att2.detik_telat_masuk,0) AS detik_telat_masuk,
            if(att2.is_late,att2.is_late,0) AS is_late,
            if(att2.is_early,att2.is_early,0) AS is_early,
            if(att2.counter_hadir,att2.counter_hadir,0) AS counter_hadir
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir) AS counter_hadir
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                o.counter_hadir
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id
          ) summ";*/
        
        /*$sql = "SELECT $ttl AS sum_waktu_telat_masuk, SUM(att3.detik_telat_masuk) AS sum_detik_telat_masuk, SUM(att3.is_late) AS sum_is_late, SUM(att3.counter_hadir) AS sum_counter_hadir FROM (
            SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.is_same,
            att2.opt_keterangan,
            opt.content AS keterangan,
            TIME_FORMAT(SEC_TO_TIME(att2.detik_telat_masuk),'$fmt_time') AS waktu_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,NULL,TIME_FORMAT(SEC_TO_TIME(att2.detik_telat_masuk*att2.late_is_ignored),'$fmt_time')) AS waktu_telat_masuk,
            att2.detik_telat_masuk AS detik_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,0,att2.detik_telat_masuk*att2.late_is_ignored) AS detik_telat_masuk,
            att2.is_late AS is_late_actual,
            IF(gen_lbr2.is_holiday,0,att2.is_late*att2.late_is_ignored) AS is_late,
            att2.is_early AS is_early_actual,
            IF(gen_lbr2.is_holiday,0,att2.is_early*att2.late_is_ignored) AS is_early, 
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl,
            IF(IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)<att2.counter_hadir_opt,att2.counter_hadir_opt,IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)) AS counter_hadir,
            att2.late_is_ignored
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir_att) AS counter_hadir_att,
              MAX(att.counter_hadir_opt) AS counter_hadir_opt,
              MAX(att.late_is_ignored) AS late_is_ignored
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir_att,
                NULL AS counter_hadir_opt,
                NULL AS late_is_ignored
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(a.max_time = a.min_time,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                NULL,
                o.counter_hadir,
                o.late_is_ignored
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id) att3";*/
        
        /*$sql = "SELECT $ttl AS sum_waktu_telat_masuk, SUM(smmry.detik_telat_masuk) AS sum_detik_telat_masuk, SUM(smmry.counter_late) AS sum_is_late, SUM(smmry.counter_hadir) AS sum_counter_hadir FROM (
            SELECT att3.*,
          IF(att3.is_late*att3.late_is_counted IS NULL,0,att3.is_late*att3.late_is_counted) AS counter_late,
          IF(att3.detik_telat_masuk_actual*att3.late_is_counted IS NULL,0,att3.detik_telat_masuk_actual*att3.late_is_counted) AS detik_telat_masuk,
          TIME_FORMAT(SEC_TO_TIME(IF(att3.detik_telat_masuk_actual*att3.late_is_counted>0,att3.detik_telat_masuk_actual*att3.late_is_counted,NULL)),'$fmt_time') AS waktu_telat_masuk
        FROM (  
          SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.opt_keterangan,
            opt.content AS keterangan,
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            att2.is_same,
            att2.is_late,
            att2.is_early,
            att2.detik_telat_masuk AS detik_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl,
            IF(IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)) IS NULL, 0, IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att))) AS counter_hadir,
            IF(gen_lbr2.is_holiday,0,IF(att2.late_is_counted IS NULL,IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,0,1)),att2.late_is_counted)) AS late_is_counted
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir_att) AS counter_hadir_att,
              MAX(att.counter_hadir_opt) AS counter_hadir_opt,
              MAX(att.late_is_counted) AS late_is_counted
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir_att,
                NULL AS counter_hadir_opt,
                NULL AS late_is_counted
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(a.max_time = a.min_time,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                NULL,
                o.counter_hadir,
                o.late_is_counted
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id)
            att3) smmry";*/
        
        $sql = "SELECT $ttl AS sum_waktu_telat_masuk, SUM(smmry.detik_telat_masuk) AS sum_detik_telat_masuk, SUM(smmry.counter_late) AS sum_is_late, SUM(smmry.counter_hadir) AS sum_counter_hadir FROM (
            SELECT att3.*,
          IF(att3.is_late*att3.late_is_counted IS NULL,0,att3.is_late*att3.late_is_counted) AS counter_late,
          IF(att3.detik_telat_masuk_actual*att3.late_is_counted IS NULL,0,att3.detik_telat_masuk_actual*att3.late_is_counted) AS detik_telat_masuk,
          TIME_FORMAT(SEC_TO_TIME(IF(att3.detik_telat_masuk_actual*att3.late_is_counted>0,att3.detik_telat_masuk_actual*att3.late_is_counted,NULL)),'$fmt_time') AS waktu_telat_masuk
        FROM (  
          SELECT gen_lbr2.tanggal,
            gen_lbr2.hari,
            att2.jam_masuk,
            att2.jam_keluar,
            att2.opt_keterangan,
            opt.content AS keterangan,
            gen_lbr2.is_holiday,
            gen_lbr2.desc_holiday,
            att2.is_same,
            att2.is_late,
            att2.is_early,
            att2.detik_telat_masuk AS detik_telat_masuk_actual,
            IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,1,0)) AS is_blank,
            gen_lbr2.tgl,
            IF(IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att)) IS NULL, 0, IF(att2.counter_hadir_opt IS NOT NULL, att2.counter_hadir_opt, IF(gen_lbr2.is_holiday,0,att2.counter_hadir_att))) AS counter_hadir,
            IF(gen_lbr2.is_holiday,0,IF(att2.late_is_counted IS NULL,IF(gen_lbr2.is_holiday,0,IF(is_late IS NULL,0,1)),att2.late_is_counted)) AS late_is_counted
            FROM (
              SELECT gen_lbr.*
              FROM (  
                SELECT gen.*,
                lbr.deskripsi AS desc_holiday,
                IF(lbr.deskripsi IS NULL,0,1) AS is_holiday
                FROM (  
                    SELECT DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'$fmt_date') AS tanggal,
                    DATE_FORMAT(DATE_ADD(MAKEDATE(z.tahun, z.gen_date), INTERVAL (z.bulan-1) MONTH),'%a') AS hari,
                    gen_date AS tgl
                    FROM (
                      SELECT gen_date,
                      $tahun AS tahun,
                      $bulan AS bulan
                      FROM tabel_helper
                    ) z
                    GROUP BY z.gen_date
                    ORDER BY z.gen_date
                ) gen
                LEFT OUTER JOIN (
                  SELECT *
                  FROM libur
                  ORDER BY libur.tgl DESC
                ) lbr
                ON ((UPPER(gen.hari) = UPPER(lbr.hari)) AND (lbr.tgl IS NULL) AND lbr.expired_time IS NULL)
                OR ((gen.tanggal = DATE_FORMAT(lbr.tgl,'$fmt_date')) AND (lbr.hari IS NULL) AND lbr.expired_time IS NULL)
              ) gen_lbr
              GROUP BY gen_lbr.tanggal
            ) gen_lbr2
            LEFT OUTER JOIN (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan,
              MAX(att.counter_hadir_att) AS counter_hadir_att,
              MAX(att.counter_hadir_opt) AS counter_hadir_opt,
              MAX(att.late_is_counted) AS late_is_counted
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan,
                1 AS counter_hadir_att,
                NULL AS counter_hadir_opt,
                NULL AS late_is_counted
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,1,0) AS is_same
                  FROM attendance a
                  WHERE a.user_id = $user_id
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan,
                NULL,
                o.counter_hadir,
                o.late_is_counted
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.user_id = $user_id
              ) att
              GROUP BY att.tanggal
            ) att2
            ON gen_lbr2.tanggal = att2.tanggal
            LEFT OUTER JOIN opt_keterangan opt
            ON att2.opt_keterangan = opt.opt_keterangan_id)
            att3) smmry";
        
        //$query = $this->db->query($sql, array($fmt_date, (integer)$bulan, (integer)$tahun, (integer)$user_id, (integer)$user_id));
        $query = $this->db->query($sql);
        $return = NULL;
        if ($query->num_rows() == 1) {
            $return = $query->row();
        }
        $this->db->close();
        return $return;
    }
    
    function insert_keterangan($user_id,$tahun,$bulan,$arr_ket) {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        $tbl = 'keterangan';
        $col_user_id = 'user_id';
        $col_tanggal = 'tgl';
        $col_opt_keterangan = 'opt_keterangan';
        
        $this->load->database('default');
        $this->db->trans_start();
                
        $row_inserted = 1;
        $row_deleted = 0;
        foreach ($arr_ket as $key => $value) {
            if ((isset($value)) && ($value > 0)) {
                $strcek = "SELECT * FROM $tbl WHERE expired_time IS NULL
                    AND $col_user_id = $user_id
                    AND 
                    $col_tanggal = DATE_ADD(MAKEDATE($tahun, $key), INTERVAL ($bulan-1) MONTH)
                    AND $col_opt_keterangan = $value";

                $querycek = $this->db->query($strcek);

                if ($querycek->num_rows == 0) {

                    $str = "UPDATE $tbl
                        SET expired_time = CURRENT_TIMESTAMP,
                        modified_by = $current_logged_in_user
                        WHERE expired_time IS NULL AND $col_user_id = $user_id
                        AND $col_tanggal = DATE_ADD(MAKEDATE($tahun, $key), INTERVAL ($bulan-1) MONTH)";

                    $query = $this->db->query($str);

                    $data_mysql = array(
                        $col_user_id => $user_id,
                        $col_opt_keterangan => $value,
                        'created_by' => $current_logged_in_user
                    );

                    $this->db->set($col_tanggal, 'DATE_ADD(MAKEDATE('.$tahun.', '.$key.'), INTERVAL ('.$bulan.'-1) MONTH)', FALSE);
                    $this->db->insert($tbl, $data_mysql);
                    $row_inserted++;
                }
            } else {
                $strcek = "SELECT * FROM $tbl WHERE expired_time IS NULL
                    AND $col_user_id = $user_id
                    AND 
                    $col_tanggal = DATE_ADD(MAKEDATE($tahun, $key), INTERVAL ($bulan-1) MONTH)";

                $querycek = $this->db->query($strcek);

                if ($querycek->num_rows > 0) {
                    $str = "UPDATE $tbl
                        SET expired_time = CURRENT_TIMESTAMP,
                        modified_by = $current_logged_in_user
                        WHERE expired_time IS NULL AND $col_user_id = $user_id
                        AND $col_tanggal = DATE_ADD(MAKEDATE($tahun, $key), INTERVAL ($bulan-1) MONTH)";

                    $query = $this->db->query($str);
                    $row_deleted++;
                }
            }
        }
        $this->db->trans_complete();
        return ($row_inserted*100+$row_deleted); //AMRNOTE: FALSE == 100
    }
    
    function get_summary_of_keterangan($user_id,$tahun,$bulan,$empty_counter = FALSE) {
        if (!$empty_counter && (empty($user_id) || empty($tahun) || empty($bulan))) {
            return NULL;
        }
        
        if (!$empty_counter && !$this->is_attendance_data_exist($user_id, $tahun, $bulan)) {
            return NULL;
        }
        
        if ($empty_counter && ($user_id == 'NOTUSE') && ($tahun == 'NOTUSE') && ($bulan == 'NOTUSE')) {
            $sql = "SELECT * FROM (
                SELECT o.opt_keterangan_id AS id,
                o.content AS keterangan,
                NULL AS jumlah,
                o.expired_time
                FROM opt_keterangan o
                WHERE o.expired_time IS NULL
                GROUP BY o.opt_keterangan_id
                ) s
                WHERE s.expired_time IS NULL OR s.jumlah > 0";
        } else {
            $sql = "SELECT * FROM (
                SELECT o.opt_keterangan_id AS id,
                o.content AS keterangan,
                count(a.user_id) AS jumlah,
                o.expired_time
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                SELECT k.*
                FROM keterangan k
                WHERE k.user_id = $user_id
                AND k.tgl > DATE_ADD(MAKEDATE($tahun, 31), INTERVAL ($bulan-2) MONTH)
                AND k.tgl < DATE_ADD(MAKEDATE($tahun, 1), INTERVAL ($bulan) MONTH)
                AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY o.opt_keterangan_id
                ) s
                WHERE s.expired_time IS NULL OR s.jumlah > 0";
        }
         
        $query = $this->db->query($sql);
        $return = NULL;
        if ($query->num_rows() > 0) {
            $return = $query->result();
        }
        $this->db->close();
        return $return;
    }
    
    function get_summary_of_keterangan_with_group($user_id,$tahun,$bulan,$empty_counter = FALSE,$digit_order_no = 1) {
        if (!$empty_counter && (empty($user_id) || empty($tahun) || empty($bulan))) {
            return NULL;
        }
        
        if (!$empty_counter && !$this->is_attendance_data_exist($user_id, $tahun, $bulan)) {
            return NULL;
        }
        
        if ($empty_counter && ($user_id == 'NOTUSE') && ($tahun == 'NOTUSE') && ($bulan == 'NOTUSE')) {
            $sql = "SELECT * FROM (
                SELECT o.opt_keterangan_id AS id,
                o.reff AS keterangan,
                NULL AS jumlah,
                o.expired_time
                FROM opt_keterangan o
                GROUP BY SUBSTRING(o.order_no,1,$digit_order_no)
                ) s
                WHERE s.expired_time IS NULL OR s.jumlah > 0";
        } else {
            $sql = "SELECT * FROM (
                SELECT o.opt_keterangan_id AS id,
                o.reff AS keterangan,
                count(a.user_id) AS jumlah,
                o.expired_time
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                    SELECT k.*
                    FROM keterangan k
                    WHERE k.user_id = $user_id
                    AND k.tgl > DATE_ADD(MAKEDATE($tahun, 31), INTERVAL ($bulan-2) MONTH)
                    AND k.tgl < DATE_ADD(MAKEDATE($tahun, 1), INTERVAL ($bulan) MONTH)
                    AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY SUBSTRING(o.order_no,1,$digit_order_no)
                ) s
                WHERE s.expired_time IS NULL OR s.jumlah > 0";
        }
        
        $query = $this->db->query($sql);
        $return = NULL;
        if ($query->num_rows() > 0) {
            $return = $query->result();
        }
        $this->db->close();
        return $return;
    }
    
    function get_attendance_data_on_date($tahun,$bulan,$tanggal,$department = array()) {
        if (empty($tahun) || empty($bulan) || empty($tanggal)) {
            return NULL;
        }
        
        /*if (!$this->is_attendance_data_exist($user_id, $tahun, $bulan)) {
            return NULL;
        }*/
        
        /*$fmt_date = '%d/%m/%Y';
        $fmt_time = '%H:%i';
        $late_limit = '07:40';
        $early_limit = '16:30';
        $time_divider = '12:00';*/
        
        $filter_default_dept_id = "";
        if ($department != array()) {
            $filter_default_dept_id = "IN (";
            foreach ($department as $key => $value) {
                $filter_default_dept_id = $filter_default_dept_id."$key,";
            }
            $filter_default_dept_id = substr($filter_default_dept_id, 0, -1).")";
        } else {
            $filter_default_dept_id = "LIKE '%'";
        }
            
        $fmt_date = $this->Parameter->get_value('FORMAT_TGL');
        $fmt_time = $this->Parameter->get_value('FORMAT_JAM');
        $late_limit = $this->Parameter->get_value('JAM_MASUK');
        $early_limit = $this->Parameter->get_value('JAM_KELUAR');
        $time_divider = $this->Parameter->get_value('JAM_TENGAH');
        $detik_pertimbangan = $this->Parameter->get_value('DETIK_PERTIMBANGAN');
        
        $this->load->database('default');
        
        /*$sql = "SELECT u.user_id,
            u.name AS nama,
            u.default_dept_id,
            d.dept_id,
            d.dept_name AS dept_name,
            aa.tanggal,
            aa.user_id,
            aa.jam_masuk AS jam_masuk,
            aa.is_late AS is_late,
            aa.detik_telat_masuk,
            TIME_FORMAT(SEC_TO_TIME(aa.detik_telat_masuk),'$fmt_time') AS waktu_telat_masuk,
            aa.jam_keluar AS jam_keluar,
            aa.is_early AS is_early,
            aa.opt_keterangan,
            o.content AS keterangan
            FROM userinfo u
            LEFT OUTER JOIN
            (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(a.max_time = a.min_time,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(a.max_time = a.min_time,1,0) AS is_same
                  FROM attendance a
                  WHERE a.date = DATE_ADD(MAKEDATE($tahun, $tanggal), INTERVAL ($bulan-1) MONTH)
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.tgl = DATE_ADD(MAKEDATE($tahun, $tanggal), INTERVAL ($bulan-1) MONTH)
              ) att
              GROUP BY att.user_id
            ) aa
            ON u.user_id = aa.user_id
            LEFT OUTER JOIN department d
            ON u.default_dept_id = d.dept_id
            LEFT OUTER JOIN opt_keterangan o
            ON aa.opt_keterangan = o.opt_keterangan_id
            WHERE
            u.default_dept_id $filter_default_dept_id
            ORDER BY u.name ASC";*/
        
        $sql = "SELECT u.user_id,
            u.name AS nama,
            u.default_dept_id,
            d.dept_id,
            d.dept_name AS dept_name,
            aa.tanggal,
            aa.user_id,
            aa.jam_masuk AS jam_masuk,
            aa.is_late AS is_late,
            aa.detik_telat_masuk,
            TIME_FORMAT(SEC_TO_TIME(aa.detik_telat_masuk),'$fmt_time') AS waktu_telat_masuk,
            aa.jam_keluar AS jam_keluar,
            aa.is_early AS is_early,
            aa.opt_keterangan,
            o.content AS keterangan
            FROM userinfo u
            LEFT OUTER JOIN
            (
              SELECT att.user_id,
              att.tanggal,
              att.is_same,
              att.jam_masuk,
              att.jam_keluar,
              att.is_late,
              att.detik_telat_masuk,
              att.is_early,
              MAX(att.opt_keterangan) AS opt_keterangan
              FROM (
                SELECT aa.user_id,
                aa.tanggal,
                aa.is_same,
                DATE_FORMAT(aa.enter_time,'$fmt_time') AS jam_masuk,
                DATE_FORMAT(aa.leave_time,'$fmt_time') AS jam_keluar,
                IF(aa.enter_time > '$late_limit', 1, 0) AS is_late,
                TIME_TO_SEC(IF(TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit') > 0, TIMEDIFF(DATE_FORMAT(aa.enter_time,'$fmt_time'),'$late_limit'), NULL)) AS detik_telat_masuk,
                IF(aa.leave_time < '$early_limit', 1, 0) AS is_early,
                NULL AS opt_keterangan
                FROM (
                  SELECT a.user_id,
                  DATE_FORMAT(a.date,'$fmt_date') AS tanggal,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,IF(TIMEDIFF(DATE_FORMAT(a.min_time,'$fmt_time'),'$time_divider') >= 0,NULL,a.min_time),a.min_time) AS enter_time,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,IF(TIMEDIFF('$time_divider',DATE_FORMAT(a.max_time,'$fmt_time')) > 0,NULL,a.max_time),a.max_time) AS leave_time,
                  IF(TIME_TO_SEC(TIMEDIFF(a.max_time,a.min_time)) <= $detik_pertimbangan,1,0) AS is_same
                  FROM attendance a
                  WHERE a.date = DATE_ADD(MAKEDATE($tahun, $tanggal), INTERVAL ($bulan-1) MONTH)
                ) aa
                UNION
                SELECT k.user_id,
                DATE_FORMAT(k.tgl,'$fmt_date') AS tanggal,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                k.opt_keterangan
                FROM keterangan k
                LEFT OUTER JOIN opt_keterangan o
                ON k.opt_keterangan = o.opt_keterangan_id
                WHERE k.expired_time IS NULL
                AND k.tgl = DATE_ADD(MAKEDATE($tahun, $tanggal), INTERVAL ($bulan-1) MONTH)
              ) att
              GROUP BY att.user_id
            ) aa
            ON u.user_id = aa.user_id
            LEFT OUTER JOIN department d
            ON u.default_dept_id = d.dept_id
            LEFT OUTER JOIN opt_keterangan o
            ON aa.opt_keterangan = o.opt_keterangan_id
            WHERE
            u.default_dept_id $filter_default_dept_id
            ORDER BY u.name ASC";
        
        //$query = $this->db->query($sql, array($fmt_date, (integer)$bulan, (integer)$tahun, (integer)$user_id, (integer)$user_id));
        $query = $this->db->query($sql);
        $return = NULL;
        if ($query->num_rows() > 1) {
            $return = $query->result();
        }
        $this->db->close();
        return $return;
    }
    
    
    
    function get_holidays_list_in_a_year($tahun) {
        if (empty($tahun)) {
            return NULL;
        }
        
        $tbl = 'libur';
        $col_tgl = 'tgl';
        $col_tgl_alias = 'tanggal';
        $col_deskripsi = 'deskripsi';
        //$col_tgl_alias = 'year';
        
        $this->load->database('default');
        $sql = "SELECT DATE_FORMAT(l.$col_tgl,'%Y/%c/%e') AS $col_tgl_alias, l.$col_deskripsi, DATE_FORMAT(l.$col_tgl,'%d %b %Y') AS $col_tgl, o.content AS jenis
            FROM $tbl l
            LEFT OUTER JOIN opt_libur o
            ON l.opt_libur_id = o.opt_libur_id    
            WHERE DATE_FORMAT(l.$col_tgl,'%Y') = $tahun
            AND l.hari IS NULL
            AND l.expired_time IS NULL
            ORDER BY l.$col_tgl ASC";
        $query = $this->db->query($sql);
        $arr = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr[$obj->$col_tgl.'|||'.$obj->$col_tgl_alias] = $obj->$col_deskripsi.'|||'.$obj->jenis;
            }
        } else {
            $arr = NULL;
        }
        $this->db->close();
        return $arr;
    }
    
    function get_no_work_days() {
        /*
        INSERT INTO `libur` (`libur_id`, `expired_time`, `tipe`, `tgl`, `hari`, `deskripsi`) VALUES
        (NULL, NULL, 'CONTINUE', NULL, 'SUN', 'Libur'),
        (NULL, NULL, 'CONTINUE', NULL, 'SAT', 'Libur'); 
        */
        
        $tbl = 'libur';
        $col1 = 'hari';
        //$col_tgl_alias = 'year';
        
        $this->load->database('default');
        $sql = "SELECT * 
            FROM $tbl
            WHERE $col1 IS NOT NULL
            AND expired_time IS NULL";
        $query = $this->db->query($sql);
        $arr = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr[$obj->$col1] = $obj->$col1;
            }
        }
        $this->db->close();
        return $arr;
    }
    
    function insert_holidays($deskripsi,$tahun,$bulan,$tgl,$opt) {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        if (empty($deskripsi) || empty($tahun) || empty($bulan) || empty($tgl) || empty($opt)) {
            return 0;
        }
        
        $tbl = 'libur';
        $col_deskripsi = 'deskripsi';
        $col_tanggal = 'tgl';
        $col_opt_libur = 'opt_libur_id';
        
        $this->load->database('default');
        $this->db->trans_start();
        
        $strcek = "SELECT * FROM $tbl WHERE expired_time IS NULL
                    AND $col_tanggal = DATE_ADD(MAKEDATE($tahun, $tgl), INTERVAL ($bulan-1) MONTH)";

        $querycek = $this->db->query($strcek);

        if ($querycek->num_rows == 0) {
        
            $data_mysql = array(
                $col_deskripsi => $deskripsi,
                $col_opt_libur => $opt,
                'created_by' => $current_logged_in_user
            );

            $this->db->set($col_tanggal, 'DATE_ADD(MAKEDATE('.$tahun.', '.$tgl.'), INTERVAL ('.$bulan.'-1) MONTH)', FALSE);
            $this->db->insert($tbl, $data_mysql);
            
        } else {
            return -1; //AMRNOTE: DATA SUDAH ADA
        }
                    
        $this->db->trans_complete();
        
        return 1; //AMRNOTE: FALSE == 100
    }
    
    function delete_holidays($tahun,$bulan,$tgl) {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        if (empty($tahun) || empty($bulan) || empty($tgl)) {
            return FALSE;
        }
        
        $tbl = 'libur';
        $col_deskripsi = 'deskripsi';
        $col_tanggal = 'tgl';
        $col_opt_libur = 'opt_libur_id';
        
        $this->load->database('default');
        $this->db->trans_start();
        
        $strcek = "SELECT * FROM $tbl WHERE expired_time IS NULL
                    AND $col_tanggal = DATE_ADD(MAKEDATE($tahun, $tgl), INTERVAL ($bulan-1) MONTH)";

        $querycek = $this->db->query($strcek);

        if ($querycek->num_rows > 0) {
            $str = "UPDATE $tbl
                    SET expired_time = CURRENT_TIMESTAMP,
                    modified_by = $current_logged_in_user
                    WHERE expired_time IS NULL
                    AND $col_tanggal = DATE_ADD(MAKEDATE($tahun, $tgl), INTERVAL ($bulan-1) MONTH)";

            $query = $this->db->query($str);
            
            $this->db->trans_complete();
            
            return TRUE;
        }
        
        $this->db->trans_complete();
        
        return FALSE; //AMRNOTE: FALSE == 100
    }
    
    function get_holidays_type() {
        $tbl = 'opt_libur';
        $col1 = 'content';
        $col2 = 'opt_libur_id';
        $col_order = 'order_no';
        
        $this->load->database('default');
        /*$sql = "SELECT * 
            FROM $tbl
            WHERE $col1 IS NOT NULL
            AND expired_time IS NULL";*/
        $sql = "SELECT * 
            FROM $tbl
            WHERE expired_time IS NULL
            ORDER BY $col_order ASC";
        $query = $this->db->query($sql);
        $arr = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $arr[$obj->$col2] = $obj->$col1;
            }
        }
        $this->db->close();
        return $arr;
    }
    
    function get_holidays_list($tahun = NULL, $bulan = NULL, $tgl = NULL) {
        $tbl = 'libur';
        $col_tgl = 'tgl';
        $col_tgl_alias = 'tanggal';
        $col_deskripsi = 'deskripsi';
        //$col_tgl_alias = 'year';
        
        $notintgl = '';
        if (!empty($tahun) && !empty($bulan) && !empty($tgl)) {
            $notintgl = " AND $col_tgl != DATE_ADD(MAKEDATE($tahun, $tgl), INTERVAL ($bulan-1) MONTH) ";
        }
        
        $this->load->database('default');
        $sql = "SELECT DATE_FORMAT(l.$col_tgl,'%Y-%m-%d') AS $col_tgl_alias
            FROM $tbl l
            WHERE l.hari IS NULL
            AND l.expired_time IS NULL$notintgl
            ORDER BY l.$col_tgl ASC";
        $query = $this->db->query($sql);
        $str = '';
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $obj) {
                $str = $str.$obj->$col_tgl_alias.'|';
            }
        } else {
            $str = '';
        }
        if (strlen($str) > 0) {
            $str = substr($str, 0, (strlen($str)-1));
        }
        $this->db->close();
        
        return $str;
    }
    
    function get_holidays_detail($tahun,$bulan,$tgl) {
        if (empty($tahun) || empty($bulan) || empty($tgl)) {
            return FALSE;
        }
        
        $tbl = 'libur';
        $col_tgl = 'tgl';
        $col_tgl_alias = 'tanggal';
        $col_deskripsi = 'deskripsi';
        //$col_tgl_alias = 'year';
        
        $this->load->database('default');
        $sql = "SELECT DATE_FORMAT(l.$col_tgl,'%a, %d/%m/%Y') AS $col_tgl_alias, l.$col_deskripsi, DATE_FORMAT(l.$col_tgl,'%d %b %Y') AS $col_tgl, o.content AS jenis, o.opt_libur_id
            FROM $tbl l
            LEFT OUTER JOIN opt_libur o
            ON l.opt_libur_id = o.opt_libur_id    
            WHERE l.hari IS NULL
            AND l.expired_time IS NULL
            AND $col_tgl = DATE_ADD(MAKEDATE($tahun, $tgl), INTERVAL ($bulan-1) MONTH) 
            ORDER BY l.$col_tgl ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $obj = $query->row();
            $str = $obj->$col_tgl_alias.'|||'.$obj->$col_deskripsi.'|||'.$obj->opt_libur_id;
        } else {
            $str = NULL;
        }
        $this->db->close();
        return $str;
    }
    
    function update_holidays($deskripsi,$tahun,$bulan,$tgl,$opt,$tahun_before,$bulan_before,$tgl_before) {
        $current_logged_in_user = $this->flexi_auth->get_user_id();
        
        if (empty($deskripsi) || empty($tahun) || empty($bulan) || empty($tgl) || empty($opt) || empty($tahun_before) || empty($bulan_before) || empty($tgl_before)) {
            return 0;
        }
        
        $tbl = 'libur';
        $col_id = 'libur_id';
        $col_deskripsi = 'deskripsi';
        $col_tanggal = 'tgl';
        $col_opt_libur = 'opt_libur_id';
        
        $this->load->database('default');
        $this->db->trans_start();
        
        $strcek = "SELECT * FROM $tbl WHERE expired_time IS NULL
                    AND $col_tanggal = DATE_ADD(MAKEDATE($tahun_before, $tgl_before), INTERVAL ($bulan_before-1) MONTH)";

        $querycek = $this->db->query($strcek);

        if ($querycek->num_rows == 1) {
            
            $obj_cek = $querycek->row();
            
            $str = "UPDATE $tbl
                SET expired_time = CURRENT_TIMESTAMP,
                modified_by = $current_logged_in_user
                WHERE expired_time IS NULL AND $col_id = ".$obj_cek->$col_id."
                AND $col_tanggal = DATE_ADD(MAKEDATE($tahun_before, $tgl_before), INTERVAL ($bulan_before-1) MONTH)";

            $query = $this->db->query($str);

            $data_mysql = array(
                $col_deskripsi => $deskripsi,
                $col_opt_libur => $opt,
                'created_by' => $current_logged_in_user
            );

            $this->db->set($col_tanggal, 'DATE_ADD(MAKEDATE('.$tahun.', '.$tgl.'), INTERVAL ('.$bulan.'-1) MONTH)', FALSE);
            $this->db->insert($tbl, $data_mysql);
            
        } else {
            return -1; //AMRNOTE: DATA SUDAH ADA
        }
                    
        $this->db->trans_complete();
        
        return 1; //AMRNOTE: FALSE == 100
    }
}

?>
