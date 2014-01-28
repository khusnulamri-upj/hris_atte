<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="row">
        <div class="one whole padded bounceInLeft animated">
          <div class="row white box">
            <div class="three-up two-up-small-tablet half-padded">
              <h5 class="zero"><i class="icon-keyboard"></i> <a href="<?php echo site_url('attendance/entry')?>">Input Presensi</a></h5>
              <p>Input keterangan (sakit/ijin/cuti) pada daftar presensi masing-masing karyawan/dosen</p>
            </div>
            <div class="three-up two-up-small-tablet half-padded">
              <h5 class="zero"><i class="icon-book"></i> <a href="<?php echo site_url('attendance/reportc')?>">Laporan Presensi Per Tanggal</a></h5>
              <p>Laporan presensi untuk semua karyawan/dosen pada satu tanggal tertentu</p>
            </div>
            <div class="three-up two-up-small-tablet half-padded">
              <h5 class="zero"><i class="icon-book"></i> <a href="<?php echo site_url('attendance/reporta')?>">Laporan Presensi Per Bulan Per Karyawan/Dosen</a></h5>
              <p>Laporan presensi karyawan/dosen dalam satu bulan tertentu yang ditampilkan secara detail</p>
            </div>
            <div class="three-up two-up-small-tablet half-padded">
              <h5 class="zero"><i class="icon-book"></i> <a href="<?php echo site_url('attendance/reportb')?>">Laporan Presensi Per Tahun Per Departemen</a></h5>
              <p>Laporan presensi karyawan/dosen pada satu departemen dalam satu tahun dan ditampilkan hanya berupa resume</p>
            </div>
            <div class="three-up two-up-small-tablet half-padded"><br/><br/><br/><br/></div>
            <div class="three-up two-up-small-tablet half-padded"><br/><br/><br/><br/></div>
            <div class="three-up two-up-small-tablet half-padded"><br/><br/><br/><br/></div>
            <div class="three-up two-up-small-tablet half-padded"><br/><br/><br/><br/></div>
            <div class="three-up two-up-small-tablet half-padded"><br/><br/><br/><br/></div>
          </div>
        </div>
      </div>
    </div>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>