  <body>
    <header class="padded">
      <div class="container">
        <div class="row">
          <div class="one half">
            <h2 class="logo"><a href="<?php echo base_url(); ?>" target="_parent"><img src="<?php echo base_url($this->Parameter->get_value('APP_SIGN')); ?>" alt="<?php echo $this->Parameter->get_value('APP_NAME') ?>"><?php echo $this->Parameter->get_value('APP_NAME') ?></a></h2>
          </div>
          <?php
          $user = $this->flexi_auth->get_user_by_identity_row_array();
          $user_info = isset($user['upro_first_name'])?$user['upro_first_name']:'';
          if (isset($user_info)) {
              $user_info .= ' ';
          }
          $user_info .= isset($user['upro_last_name'])?$user['upro_last_name']:'';
          ?>
          <div class="one half">
            <p class="double pad-top no-pad-small-tablet align-right align-left-small-tablet">Selamat Datang, <a href="<?php echo site_url('members/update_account')?>"><?php echo $user_info; ?></a><br><?php echo $this->flexi_auth->get_user_group(); ?> | <a href="<?php echo site_url('user/logout')?>" rel="next">Logout&nbsp;</a></p>
          </div>
        </div>