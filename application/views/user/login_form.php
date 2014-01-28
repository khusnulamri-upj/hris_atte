<?php
$this->load->view('template_groundwork/head');
$this->load->view('user/login_form_body_header');
$this->load->view('user/login_form_body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <!--<h3 class="zero museo-slab align-center">Login</h3>-->
            <!--<p class="quicksand">Login</p>-->
          </div>
        </div>
      </div>
      <?php if (!empty($message)) { ?>
      <div class="row bounceInLeft animated">
        <div class="one half centered align-center">
          <div class="row"><?php echo $message; ?></div>
        </div>
      </div>
      <?php } ?>
      <br/><br/>
      <div class="row bounceInRight animated">
        <div class="one fourth centered padded">
          <form action="<?php echo current_url()?>" method="post">
            <fieldset>
              <div class="row">
                <div class="one whole padded">
                  <label for="name">Email</label>
                  <input type="text" name ="login_identity" value="">
                </div>
              </div>
              <div class="row">
                <div class="one whole padded">
                  <label for="month">Password</label>
                  <input type="password" name="login_password" value="">
                </div>
              </div>
              <div class="row">
                <div class="one whole padded">
                  <input type="submit" name="login_user" value="Login">
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
$this->load->view('user/login_form_body_footer');
?>