        <nav role="navigation" class="nav gap-top">
          <ul role="menubar">
            <li class="desktop-only"><button><i class="icon-group"></i></button></li>
            <?php if (
                    ($this->flexi_auth->is_privileged('ins_ket')) ||
                    ($this->flexi_auth->is_privileged('vw_daily_rpt')) ||
                    ($this->flexi_auth->is_privileged('vw_mnth_prsn_rpt')) ||
                    ($this->flexi_auth->is_privileged('vw_mnth_prsn_rpt_all')) ||
                    ($this->flexi_auth->is_privileged('vw_year_dept_rpt')) ||
                    ($this->flexi_auth->is_privileged('vw_year_dept_rpt_all'))
                    ) { ?>
            <li role="menu">
                <button title="Presensi Karyawan/Dosen" class="half-gap-left-desktop gap-right-desktop">Presensi Karyawan/Dosen</button>
              <ul>
                <?php if (
                        ($this->flexi_auth->is_privileged('ins_ket'))
                        ) { ?>
                <li><a href="<?php echo site_url('attendance/entry')?>" title="Input Keterangan Presensi Karyawan/Dosen">Input Keterangan</a></li>
                <?php } ?>
                <?php if (
                        ($this->flexi_auth->is_privileged('vw_daily_rpt')) ||
                        ($this->flexi_auth->is_privileged('vw_mnth_prsn_rpt')) ||
                        ($this->flexi_auth->is_privileged('vw_mnth_prsn_rpt_all')) ||
                        ($this->flexi_auth->is_privileged('vw_year_dept_rpt')) ||
                        ($this->flexi_auth->is_privileged('vw_year_dept_rpt_all'))
                        ) { ?>
                <li role="menu">
                    <button title="Laporan Presensi Karyawan/Dosen">Laporan Presensi</button>
                    <ul>
                        <?php if (
                              ($this->flexi_auth->is_privileged('vw_daily_rpt'))
                              ) { ?>
                        <li><a href="<?php echo site_url('attendance/reportc')?>" title="Laporan Presensi Per Tanggal">Per Tanggal</a></li>
                        <?php } ?>
                        
                        <?php if (
                              ($this->flexi_auth->is_privileged('vw_mnth_prsn_rpt')) ||
                              ($this->flexi_auth->is_privileged('vw_mnth_prsn_rpt_all'))
                              ) { ?>
                        <li><a href="<?php echo site_url('attendance/reporta')?>" title="Laporan Presensi Per Bulan Per Karyawan/Dosen">Per Bulan Per Karyawan/Dosen</a></li>
                        <?php } ?>
                        
                        <?php if (
                              ($this->flexi_auth->is_privileged('vw_year_dept_rpt')) ||
                              ($this->flexi_auth->is_privileged('vw_year_dept_rpt_all'))
                              ) { ?>
                        <li><a href="<?php echo site_url('attendance/reportb')?>" title="Laporan Presensi Per Tahun Per Bagian/Prodi">Per Tahun Per Bagian/Prodi</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if ((!$this->flexi_auth->in_group('Bagian HRD')) && (!$this->flexi_auth->in_group('Bagian ICT'))) { ?>
                <li><a href="<?php echo site_url('import')?>" title="Transfer Data To Server">Transfer Data</a></li>
                <?php } ?>
                <?php if (($this->flexi_auth->in_group('Bagian HRD')) || ($this->flexi_auth->in_group('Master Admin'))) { ?>
                <li><a href="<?php echo site_url('attendance/entry_holidays')?>" title="Daftar Hari Libur Nasional">Daftar Libur</a></li>
                <?php } ?>
              </ul>
            </li>
            <?php } ?>
            
            <li role="menu">
              <button title="Members Area" class="half-gap-left-desktop half-gap-right-desktop">Member Area</button>
              <ul>
                <li><a href="<?php echo base_url('members/update_account')?>" title="Update Account Details">Account Details</a></li>
                <li><a href="<?php echo base_url('members/change_password')?>" title="Update Password">Update Password</a></li>
              </ul>
            </li>
            <?php if ((!$this->flexi_auth->in_group('Bagian HRD')) && (!$this->flexi_auth->in_group('Bagian ICT'))) { ?>
            <li role="menu">
              <button title="Members Area">Admin Area</button>
              <ul>
                <li><a href="<?php echo base_url('admin/manage_user_accounts')?>">Manage User Accounts</a></li>
                <li><a href="<?php echo base_url('admin/manage_user_groups')?>">Manage User Groups</a></li>
                <li><a href="<?php echo base_url('admin/manage_privileges')?>">Manage User Privileges</a></li>
              </ul>
            </li>
            <li role="menu">
              <button>Documentation</button>
              <ul>
                <li><a href="<?php echo base_url('groundwork')?>/docs/grid.html" title="Responsive grid system, grid adapters and helpers">Grid</a></li>
                <li><a href="<?php echo base_url('groundwork')?>/docs/helpers.html" title="Layout helpers, spinners and much more">Helpers</a></li>
                <li><a href="<?php echo base_url('groundwork')?>/docs/typography.html" title="Text elements, quotes, code and web fonts">Typography</a></li>
                <li role="menu">
                  <button title="Navigation, buttons, boxes, message boxes, tables, tabs, and forms">UI Elements</button>
                  <ul>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/navigation.html" title="Navigation">Navigation</a></li>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/buttons.html" title="Buttons, button groups, button menus">Buttons</a></li>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/boxes.html" title="Boxes">Boxes</a></li>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/messages.html" title="Message boxes">Message Boxes</a></li>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/tables.html" title="Tables">Tables</a></li>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/tabs.html" title="Tabs">Tabs</a></li>
                    <li><a href="<?php echo base_url('groundwork')?>/docs/forms.html" title="Form elements">Form Elements</a></li>
                  </ul>
                </li>
                <li><a href="<?php echo base_url('groundwork')?>/docs/icons.html" title="Icons">Icons</a></li>
                <li><a href="<?php echo base_url('groundwork')?>/docs/responsive-text.html" title="Responsive text and multi-line text block truncation">Responsive Text</a></li>
                <li><a href="<?php echo base_url('groundwork')?>/docs/placeholder-text.html" title="Placeholder text and placeholder fonts for rapid prototyping and wireframes">Placeholder Text</a></li>
                <li><a href="<?php echo base_url('groundwork')?>/docs/animations.html" title="Pure CSS3 Animations">Animations</a></li>
              </ul>
            </li>
            <?php } ?>
          </ul>
        </nav>
      </div>
    </header>