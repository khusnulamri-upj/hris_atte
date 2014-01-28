<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Account Registration</h3>
            <!--<p class="quicksand">Account Registration</p>-->
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
              <legend>Personal Details</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_first_name">First Name</label>
                  <?php echo form_input('register_first_name'); ?>
                </div>
              </div>
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_last_name">Last Name</label>
                  <?php echo form_input('register_last_name'); ?>
                </div>
              </div>
              <br/>
            </fieldset>
            <br/>
            <fieldset>
              <legend>Contact Details</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_phone_number">Phone Number</label>
                  <?php echo form_input('register_phone_number'); ?>
                </div>
              </div>
              <!-- AMRNOTE: di update account belum bisa update -->
              <!--<div class="row">
                <div class="four fifth padded">
                  <input type="checkbox" name="register_newsletter" checked="checked" value=1>
                  <label for="register_newsletter" class="inline">Subscribe to Newsletter</label>
                </div>
              </div>-->
              <br/>
            </fieldset>
            <br/>
            <fieldset>
              <legend>Login Details</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_email_address">Email</label>
                  <?php echo form_input('register_email_address'); ?>
                </div>
              </div>
              <!-- AMRNOTE: username = email -->
              <!--<div class="row">
                <div class="four fifth padded">
                  <label for="register_username">Username</label>
                  <?php echo form_input('register_username'); ?>
                </div>
              </div>-->
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_password">Password</label>
                  <?php echo form_password('register_password'); ?>
                </div>
              </div>
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_confirm_password">Confirm Password</label>
                  <?php echo form_password('register_confirm_password'); ?>
                </div>
              </div>
              <br/>
            </fieldset>
            <br/>
            <fieldset>
              <legend>Register</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="register_user" class="inline">Register&nbsp;</label>
                  <input type="submit" name="register_user" value="Submit">
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