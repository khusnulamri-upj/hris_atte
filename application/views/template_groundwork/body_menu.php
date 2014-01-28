<?php
if ($this->flexi_auth->is_logged_in()) {
    $this->load->view('template_groundwork/body_menu_default');
} else {
    $this->load->view('template_groundwork/body_menu_blank');
}
?>