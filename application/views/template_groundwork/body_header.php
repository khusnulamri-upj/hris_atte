<?php
if ($this->flexi_auth->is_logged_in()) {
    $this->load->view('template_groundwork/body_header_logged_in');
} else {
    $this->load->view('template_groundwork/body_header_default');
}
?>