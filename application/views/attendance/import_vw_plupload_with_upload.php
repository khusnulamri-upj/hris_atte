<?php
$this->load->view('template_groundwork/head');
$this->load->view('template_groundwork/body_header');
$this->load->view('template_groundwork/body_menu');
?>
    <script type="text/javascript" src="<?= base_url('assets/plupload') ?>/js/plupload.full.min.js"></script>
    <div class="container">
      <div class="padded">
        <div class="row">
          <div class="one whole bounceInRight animated">
            <h3 class="zero museo-slab">Transfer Data To Server</h3>
            <!--<p class="quicksand">Transfer Data To Server</p>-->
          </div>
        </div>
      </div>
      <br/>
      <div class="row padded">
        <div class="one half">
          <div id="support" style="border-style:none;">Your browser doesn't have Flash, Silverlight or HTML5 support.</div>
          <pre id="plupload-console" style="border-style:none;"><?php echo $file_exist; ?></pre>
        </div>
      </div>
      <div class="row padded">
        <div id="containerfiles" style="border-style:none; margin-left: 0px; ">
          <div class="one tenth">
            <a role="button" id="pickfiles" href="javascript:;">Select File</a>     
          </div>
          <div class="one tenth">
            <a role="button" id="processfiles" href="javascript:;">Process File</a>
          </div>
        </div>
      </div>
      <div class="row padded" id="console">
        <div class="three fifth">  
          <pre data-lang="html" id="ajaxLog"></pre>
        </div>
      </div>
      <br/>
      <br/>
    </div>
    <script type="text/javascript" src="<?= base_url()."assets/js/ajaxLog.js"; ?>"></script>
    <script type="text/javascript">function aj(){var a=<?php echo $ajaximg; ?>;var c=<?php echo $arr_controllers; ?>;var i=<?php echo $arr_interactive; ?>;sequenceRequest(c,i,a);}</script>
    <script type="text/javascript">
    // Custom example logic

    var uploader = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',
        browse_button : 'pickfiles', // you can pass in id...
        container: document.getElementById('containerfiles'), // ... or DOM Element itself
        url : '<?= base_url('assets/plupload') ?>/upload.php',
        flash_swf_url : '<?= base_url('assets/plupload') ?>/js/Moxie.swf',
        silverlight_xap_url : '<?= base_url('assets/plupload') ?>/js/Moxie.xap',

        //filters : {
        //        max_file_size : '1024mb',
        //        mime_types: [
        //                {title : "Mdb Files", extensions : "mdb"}
        //        ]
        //},

        filters : [{extensions : "mdb"}],

        // Rename files by clicking on their titles
        rename: true,

        max_file_count: 1,

        chunk_size: '<?= strtolower(ini_get('upload_max_filesize')); ?>b',

        multi_selection: false,

        init: {
                PostInit: function() {
                        document.getElementById('support').innerHTML = '';

                        document.getElementById('processfiles').onclick = function() {
                                uploader.start();
                                return false;
                        };
                },

                FilesAdded: function(up, files) {
                        plupload.each(files, function(file) {
                            $("#ajaxLog").children("ol").append("<li id=\"fileNow\">".concat("File ".concat(file.name).concat("(".concat(plupload.formatSize(file.size)).concat(") ")).concat("selected")).concat("</li>"));
                            $("#ajaxLog").scrollTop($("#ajaxLog").prop("scrollHeight"));
                        });
                },

                UploadProgress: function(up, file) {
                        $("#ajaxNow").remove();
                        $("#ajaxLog").children("ol").append("<li id=\"ajaxNow\">".concat("Uploading File ".concat(file.name).concat(" ").concat(file.percent).concat("%")).concat("</li>"));
                        $("#ajaxLog").scrollTop($("#ajaxLog").prop("scrollHeight"));
                },

                Error: function(up, err) {
                        $("#ajaxNow").remove();
                        document.getElementById('plupload-console').innerHTML += "\nError #" + err.code + ": " + err.message;
                }
        }


    });

    uploader.init();

    uploader.bind('FilesAdded', function(up) {
        if ( up.files.length > 1 && uploader.state != 2) {
            up.removeFile(up.files[0]);
            up.refresh();
            $("#fileNow").remove();
        }
    });

    uploader.bind('FileUploaded', function(up, file) {
        //if (file.percent == 100) {
            $("#ajaxNow").remove();
            $("#ajaxLog").children("ol").append("<li>".concat("File ".concat(file.name).concat(" Uploaded")).concat("</li>"));
            aj();
        //}
    });

    </script>
<?php
$this->load->view('template_groundwork/body_link');
$this->load->view('template_groundwork/body_footer');
?>