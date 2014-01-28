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
      <br/>
      <div class="row padded" id="console">
        <div class="three fifth">  
          <pre data-lang="html" id="ajaxLog"></pre>
        </div>
      </div>
      <div class="row padded">
        <div class="three fifth" id="ajaxDir" style="min-height: 8em; margin-top: -2em;"></div>
      </div>
      <br/>
    </div>
    <script type="text/javascript" src="<?= base_url()."assets/js/ajaxLog.js"; ?>"></script>
    <script type="text/javascript">function aj(){var a=<?php echo $ajaximg; ?>;var c=<?php echo $arr_controllers; ?>;var i=<?php echo $arr_interactive; ?>;sequenceRequest(c,i,a);}$(document).ready(function(){aj()});</script>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>