<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Daftar Hari Libur Nasional, Libur Bersama, dan Cuti Bersama</h3>
            <!--<p class="quicksand">Input Presensi Karyawan/Dosen</p>-->
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="one whole padded">
          
          <a role="button" class="popup-modal noicon" href="#newmodal">Tambah Hari Libur</a>

          <div id="newmodal" class="white-popup-block mfp-hide">
            <div class="five ninth padded white-bg centered">
                <div class="row">
                    <div class="one whole align-right"><a class="popup-modal-dismiss" style="text-decoration:none;" href="#"><i class="icon-remove"></i></a></div>
                </div>
                <div class="row">
                    <div class="one whole" id="newcontent"></div>
                </div>
            </div>
          </div>
        </div>
      </div>  
        
      <div class="row">
        <div class="two third padded">
          <div class="tabs vertical small-tablet">
            <ul role="tablist">
              <?php
              //$i = 0;
              foreach ($year_list as $y) {
                  //echo '<li role="tab" aria-controls="#tab'.++$i.'">'.$y.'</li>';
                  echo '<li role="tab" aria-controls="#tab'.$y.'" id="btn'.$y.'">'.$y.'</li>';
              }
              ?>
            </ul>
            <?php
            $i = 0;
            foreach ($year_list as $y) {
                if ($i > 0) {
                    $display = 'none';
                } else {
                    $display = 'block';
                }
                
                //echo '<div style="min-height: 300px; padding-left: 90px; display: '.$display.';" id="tab'.++$i.'" role="tabpanel">';
                echo '<div style="min-height: 230px; display: '.$display.';" id="tab'.$y.'" role="tabpanel">';
                ++$i;
                //echo $y;
                echo '</div>';
            }
            ?>
          </div>
        </div>
      </div>
      <br/>
    </div>
    <script type="text/javascript">/*$(document).ready(function(){<?php $i = 0; foreach ($year_list as $y) { ?>$("#tab<?php echo ++$i; ?>").html(<?php echo $ajaximg; ?>).load('<?php echo site_url('ajax_attendance/holidays_list/'.$y); ?>');<?php } ?>});*/
        $(document).ready(function(){
            <?php foreach ($year_list as $y) { ?>$("#tab<?php echo $y; ?>").html(<?php echo $ajaximg; ?>).load('<?php echo site_url('ajax_attendance/holidays_list/'.$y); ?>');<?php } ?>
            $('.popup-modal').magnificPopup({
                type: 'inline',
                preloader: false,
                modal: true,
                callbacks: {
                    open: function() {
                        // Will fire when this exact popup is opened
                        // this - is Magnific Popup object
                        $.ajax({
                            url: "<?php echo site_url('ajax_attendance/new_holidays/'); ?>",
                            dataType: 'html',
                            success: function(data){
                                $('#newcontent').html(data);
                            }
                        });
                    }
                }
                });
            $(document).on('click', '.popup-modal-dismiss', function (e) {
                e.preventDefault();
                $.magnificPopup.close();
                $('#newcontent').html('');
            });
        });
        function delete_clicked(t,d){
            var c=confirm('Apakah anda yakin menghapus "'.concat(d).concat('" ?'));
            if (c==true) {
                $.ajax({
                    url:"<?php echo site_url('ajax_attendance/delete_holidays/'); ?>".concat('/'.concat(t)),
                    success:function(r){
                        if (r != '1') {
                            alert(r);
                        }
                        repopulate(t);
                    },
                    error:function(r){
                        repopulate(t);
                    }
                });
            } else {
                repopulate(t);
            }
        };
        
        function edit_clicked(t){
            $.ajax({
                url: "<?php echo site_url('ajax_attendance/edit_holidays/'); ?>".concat('/'.concat(t)),
                dataType: 'html',
                success: function(data){
                    getCodeWindow= $('<div class="five ninth padded white-bg centered"><div class="row"><div class="one whole align-right"><a class="popup-modal-dismiss" style="text-decoration:none;" href="#"><i class="icon-remove"></i></a></div></div><div class="row">'+data+'</div></div>');
                    $.magnificPopup.open({
                            preloader: false,
                            modal: true,
                            type: 'inline',
                            items: {
                                    src: getCodeWindow
                            }
                    });
                }
            });
        };
        function repopulate(t){var a = t.split('/'); $("#tab".concat(a[0])).html(<?php echo $ajaximg; ?>).load('<?php echo site_url('ajax_attendance/holidays_list/'); ?>'.concat('/'.concat(a[0])));};
        <?php foreach ($year_list as $y) { ?> $('#btn<?php echo $y; ?>').click(function(){repopulate('<?php echo $y; ?>/0/0')});<?php } ?>
        
    </script>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>