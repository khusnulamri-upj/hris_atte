<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Update Account Details</h3>
            <!--<p class="quicksand">Update Account Details</p>-->
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
                  <label for="update_first_name">First Name</label>
                  <?php echo form_input('update_first_name', isset($user['upro_first_name'])?$user['upro_first_name']:''); ?>
                </div>
              </div>
              <div class="row">
                <div class="four fifth padded">
                  <label for="update_last_name">Last Name</label>
                  <?php echo form_input('update_last_name', isset($user['upro_last_name'])?$user['upro_last_name']:''); ?>
                </div>
              </div>
              <br/>
            </fieldset>
            <br/>
            <fieldset>
              <legend>Contact Details</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="update_phone_number">Phone Number</label>
                  <?php echo form_input('update_phone_number', isset($user['upro_phone'])?$user['upro_phone']:''); ?>
                </div>
              </div>
              <!-- AMRNOTE: update db tidak jalan, value checkbox tidak berubah -->
              <!--<div class="row">
                <div class="four fifth padded">
                  <?php
                  if ($user['upro_newsletter'] == 1) {
                      echo form_checkbox('update_newsletter', 1, TRUE);
                  } else {
                      echo form_checkbox('update_newsletter', 0, FALSE);
                  }
                  ?>
                  <label for="update_newsletter" class="inline">Subscribe to Newsletter</label>
                </div>
              </div>-->
              <br/>
            </fieldset>
            <br/>
            <fieldset>
              <legend>Login Details</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="update_email">Email</label>
                  <?php
                  $data = array(
                      'name' => 'update_email',
                      'value' => isset($user[$this->flexi_auth->db_column('user_acc', 'email')])?$user[$this->flexi_auth->db_column('user_acc', 'email')]:'',
                      'disabled' => 'disabled',
                  );
                  echo form_input($data);
                  ?>
                </div>
              </div>
              <!-- AMRNOTE: username = email -->
              <!--<div class="row">
                <div class="four fifth padded">
                  <label for="update_username">Username</label>
                  <?php echo form_input('update_username', isset($user[$this->flexi_auth->db_column('user_acc', 'username')])?$user[$this->flexi_auth->db_column('user_acc', 'username')]:''); ?>
                </div>
              </div>-->
              <div class="row">
                <div class="four fifth padded">
                  <label for="Password">Password</label>
                  <span><a href="<?php echo base_url('members/change_password'); ?>">Click here to change your password</a></span>
                </div>
              </div>
              <br/>
            </fieldset>
            <br/>
            <fieldset>
              <legend>Update Account</legend>  
              <div class="row">
                <div class="four fifth padded">
                  <label for="update_account" class="inline">Update Account&nbsp;</label>
                  <input type="submit" name="update_account" value="Submit">
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