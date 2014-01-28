<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Input Keterangan Presensi Karyawan/Dosen</h3>
            <!--<p class="quicksand">Input Keterangan Presensi Karyawan/Dosen</p>-->
          </div>
        </div>
      </div>
      <?php if (!empty($message)) { ?>
      <div class="row bounceInLeft animated">
        <div class="one half padded align-center">
          <div class="row"><p class="message dismissible<?php echo (!empty($message_type))?' '.$message_type:' error'; ?>"><?php echo $message; ?><p></div>
        </div>
      </div>
      <?php } else { ?>
      <br/>    
      <?php } ?>
      <div class="row bounceInRight animated">
        <div class="one half padded">
          <form action="<?php echo $form_action_url; ?>" method="post">
            <fieldset>
              <div class="row">
                <div class="four fifth padded">
                  <label for="name">Nama Karyawan/Dosen</label>
                  <span class="select-wrap"><?php echo form_dropdown('personnel', isset($personnel_option)?$personnel_option:array()); ?></span>
                </div>
              </div>
              <div class="row">
                <div class="two fifth padded">
                  <label for="month">Bulan</label>
                  <span class="select-wrap"><?php echo form_dropdown('month', isset($month_option)?$month_option:array(), array(date('n'))); ?></span>
                </div>
                <div class="one fifth padded">
                  <label for="year">Tahun</label>
                  <span class="select-wrap"><?php echo form_dropdown('year', isset($year_option)?$year_option:array()); ?></span>
                </div>
              </div>
              <div class="row">
                <div class="two fifth padded">
                  <input type="submit" value="Lanjut"> 
                  <!--<a role="button" href="#" rel="next" class="gap-bottom gap-right">Lanjut</a>-->
                </div>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
      <br/><br/>
    </div>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>