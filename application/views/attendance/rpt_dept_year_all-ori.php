<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Laporan Presensi Per Tahun Per Bagian/Prodi</h3>
            <!--<p class="quicksand">Laporan Presensi Per Tahun Per Bagian/Prodi</p>-->
          </div>
        </div>
      </div>
      <div class="row">
        <pre data-lang="html" id="ajaxLog"></pre>
      </div>
        <!--<input type="button" onclick="ajaxLogOnLoad()">-->
    </div>
    <script type="text/javascript" src="<?= base_url()."assets/js/ajaxLog.js"; ?>"></script>
    <script type="text/javascript">
      function ajaxLogOnLoad() {
        var ajaximg = '<?php echo $ajaximg; ?>';
        var controllers = <?php echo $arr_controllers; ?>;
        var interactive = <?php echo $arr_interactive; ?>;
        sequenceRequest(controllers, interactive, ajaximg);
      }
      $(document).ready(function() {
          ajaxLogOnLoad()
      });
    </script>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>