<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Laporan Presensi Per Tanggal</h3>
            <!--<p class="quicksand">Laporan Presensi Per Tanggal</p>-->
          </div>
        </div>
      </div>
      <div class="row">
        <div class="one whole padded">
          <div class="bounceInRight animated">
            <div class="box info">
              <div class="equalize row">
                <div class="one twelfth half-padded">Tanggal</div>
                <div class="six twelfth half-padded"><b><?php echo $tanggal?></b></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="one whole padded">
          <div class="bounceInLeft animated tablelike">
            <div class="equalize row">
              <div class="one twelfth half-padded align-center">No.</div>
              <div class="three twelfth half-padded align-center">Nama</div>
              <div class="three twelfth half-padded align-center">Bagian/Prodi</div>
              <div class="one twelfth half-padded align-center">Jam Masuk</div>
              <div class="one twelfth half-padded align-center">Jam Keluar</div>
              <div class="one twelfth half-padded align-center">Durasi Keterlambatan</div>
              <div class="two twelfth half-padded align-center">Keterangan</div>
            </div>
            <?php
            $no = 1;
            foreach ($attendance as $a) {
                $mark_is_late = '';
                $mark_is_early = '';
                if ($a->is_late) {
                    $mark_is_late = ' red';
                }
                if ($a->is_early) {
                    $mark_is_early = ' red';
                }
                echo "
                <div class=\"equalize row\">
                  <div class=\"one twelfth padded align-center\">".$no++."</div>
                  <div class=\"three twelfth padded\">".do_ucwords($a->nama)."</div>
                  <div class=\"three twelfth padded\">".do_ucwords($a->dept_name)."</div>
                  <div class=\"one twelfth padded align-center$mark_is_late\">$a->jam_masuk</div>
                  <div class=\"one twelfth padded align-center$mark_is_early\">$a->jam_keluar</div>
                  <div class=\"one twelfth padded align-center$mark_is_late\">$a->waktu_telat_masuk</div>
                  <div class=\"two twelfth half-padded align-center\">$a->keterangan</div>
                </div>
                ";
            }
            ?>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="three fifth padded">
          <div class="row">
            <div class="one half bounceInUp animated">
              <a role="button" href="<?php echo $export_xls3_url; ?>">Export ke XLS</a>
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