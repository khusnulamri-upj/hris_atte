<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Transfer Data To Server</h3>
            <!--<p class="quicksand">Transfer Data To Server</p>-->
          </div>
        </div>
      </div>
      <br/>
      <div class="row padded">
        <div class="three fifth">
          <?php echo $file_is_exist; ?>
          <a role="button" rel="next" class="gap-bottom gap-right" id="import"<?php echo $button_disabled; ?>>Transfer Data</a>
        </div>
      </div>
      <div class="row padded" id="console">
        <div class="three fifth">  
          <pre data-lang="html" id="ajaxLog"></pre>
        </div>
      </div>
      <div class="row padded" id="space" style="min-height: 9.4em; ">
      </div>
      <br/>
    </div>
    <script type="text/javascript" src="<?= base_url()."assets/js/ajaxLog.js"; ?>"></script>
    <script type="text/javascript">function aj(){var a=<?php echo $ajaximg; ?>;var c=<?php echo $arr_controllers; ?>;var i=<?php echo $arr_interactive; ?>;sequenceRequest(c,i,a);}$(document).ready(function(){$('#console').hide()});$('#import').click(function(){$('#console').show();$('#space').hide();aj();});</script>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>