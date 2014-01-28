<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Laporan Presensi Per Bulan Per Karyawan/Dosen</h3>
            <!--<p class="quicksand">Laporan Presensi Per Bulan Per Karyawan/Dosen</p>-->
          </div>
        </div>
      </div>
      <div class="row">
        <div class="three fifth padded">
          <div class="bounceInRight animated">
            <div class="box info">
              <div class="equalize row">
                <div class="two seventh half-padded">Nama Karyawan/Dosen</div>
                <div class="five seventh half-padded"><b><?php echo $personnel_name?></b></div>
              </div>
              <div class="equalize row">
                <div class="two seventh half-padded">Bagian/Prodi</div>
                <div class="five seventh half-padded"><b><?php echo $department_name?></b></div>
              </div>
              <div class="equalize row">
                <div class="two seventh half-padded">Bulan</div>
                <div class="five seventh half-padded"><b><?php echo $month_year?></b></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
      echo form_hidden('personnel', $personnel);
      echo form_hidden('year', $year);
      echo form_hidden('month', $month);
      ?>
      <div class="row">
        <div class="three fifth padded">
          <div class="bounceInLeft animated tablelike">
            <div class="equalize row">
              <div class="one seventh half-padded align-center">Tanggal</div>
              <div class="one seventh half-padded align-center">Hari</div>
              <div class="one seventh half-padded align-center">Jam Masuk</div>
              <div class="one seventh half-padded align-center">Jam Keluar</div>
              <div class="one seventh half-padded align-center">Durasi Keterlambatan</div>
              <div class="two seventh half-padded align-center">Keterangan</div>
            </div>
            <?php
            foreach ($attendance as $a) {
                $mark_is_holiday = '';
                $mark_is_late = '';
                $mark_is_early = '';
                $select_ket = '';
                $ket_libur = '';
                if ($a->is_holiday) {
                    $mark_is_holiday = ' red-bg';
                    $ket_libur = '<p class="half-gap-top">'.$a->desc_holiday.'</p>';
                }
                if ($a->is_late) {
                    $mark_is_late = ' red';
                }
                if ($a->is_early) {
                    $mark_is_early = ' red';
                }
                echo "
                <div class=\"equalize row\">
                  <div class=\"one seventh padded align-center$mark_is_holiday\">$a->tanggal</div>
                  <div class=\"one seventh padded align-center$mark_is_holiday\">$a->hari</div>
                  <div class=\"one seventh padded align-center$mark_is_holiday$mark_is_late\">$a->jam_masuk</div>
                  <div class=\"one seventh padded align-center$mark_is_holiday$mark_is_early\">$a->jam_keluar</div>
                  <div class=\"one seventh padded align-center$mark_is_holiday$mark_is_late\">$a->waktu_telat_masuk</div>
                  <div class=\"two seventh half-padded align-center$mark_is_holiday\">$a->keterangan$ket_libur</div>
                </div>
                ";
            }
            ?>
            <div class="equalize row">
              <div class="three seventh half-padded align-center asphalt-bg"></div>
              <div class="two seventh half-padded align-center asphalt-bg">Total Durasi Keterlambatan</div>
              <div class="two seventh half-padded align-center asphalt-bg"><?php echo $summary_attendance->sum_waktu_telat_masuk; ?></div>
            </div>
            <div class="equalize row">
              <div class="three seventh half-padded align-center asphalt-bg"></div>
              <div class="two seventh half-padded align-center asphalt-bg">Total Keterlambatan (hari)</div>
              <div class="two seventh half-padded align-center asphalt-bg"><?php echo $summary_attendance->sum_is_late; ?></div>
            </div>
            <div class="equalize row">
              <div class="three seventh half-padded align-center asphalt-bg"></div>
              <div class="two seventh half-padded align-center asphalt-bg">Total Kehadiran (hari)</div>
              <div class="two seventh half-padded align-center asphalt-bg"><?php echo $summary_attendance->sum_counter_hadir; ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="three fifth padded">
        <div class="box alert bounceInRight animated">
          <div class="row">
            <div class="two seventh half-padded"><b>JUMLAH KETERANGAN</b></div>
          </div>      
          <?php
          $i = 0;
          foreach ($summary_of_keterangan as $sk) {
            if (($i % 2) == 0) {
              echo "<div class=\"equalize row\">";
            }
            echo "
                <div class=\"two seventh half-padded\">$sk->keterangan</div>
                <div class=\"one seventh half-padded\">$sk->jumlah</div>
            ";
            if (($i % 2) != 0) {
              echo "</div>";
            }
            $i++;
          }
          if (($i % 2) != 0) {
            echo "</div>";
          }
          ?>
        </div>
      </div>
      <div class="row">
        <div class="three fifth padded">
          <div class="row">
            <div class="one half bounceInUp animated">
              <a role="button" href="<?php echo $export_xls1_url; ?>">Export ke XLS</a>
            </div>
          </div>
        </div>
      </div>
      <br/>
    </div>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>