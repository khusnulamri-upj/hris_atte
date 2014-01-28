<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Update Password</h3>
            <!--<p class="quicksand">Update Password</p>-->
          </div>
        </div>
      </div>
      <?php if (!empty($message)) { ?>
      <div class="row bounceInLeft animated">
        <div class="one half align-center">
          <div class="row"><?php echo $message; ?></div>
        </div>
      </div>
      <?php } ?>
      <div class="row bounceInRight animated">
        <div class="one half padded">
          <form action="<?php echo current_url()?>" method="post">
            <fieldset>
              <div class="row">
                <div class="four fifth padded">
                  <label for="current_password">Current Password</label>
                  <?php echo form_password('current_password'); ?>
                </div>
              </div>
              <div class="row">
                <div class="four fifth padded">
                  <label for="new_password">New Password</label>
                  <?php echo form_password('new_password'); ?>
                </div>
              </div>
              <div class="row">
                <div class="four fifth padded">
                  <label for="confirm_new_password">Confirm New Password</label>
                  <?php echo form_password('confirm_new_password'); ?>
                </div>
              </div>
              <div class="row">
                <div class="four fifth padded">
                  <label for="change_password" class="inline">Update Password&nbsp;</label>
                  <input type="submit" name="change_password" value="Submit">
                </div>
              </div>
              <br/>
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