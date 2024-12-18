<?php

$contentId = isset($_POST['cid']) ? ($_POST['cid']) : 0;

require_once __DIR__ . '/process_uploaded_image_to_entry.php';
$caID = $_GET['caID'];
/**
 * Update data on form submit
 */
if(isset($_POST['output_form_submit']))
{
    // process images
    if (isset($_POST['imagesToProcess'])) {

        foreach ($_POST['imagesToProcess'] as $key=>$imageToProcess) {
            process_uploaded_image_to_entry($o_main, $imageToProcess, $_POST['imagesHandle'][$key], $contentId);
        }
    }
}
?>

<div class="popupformTitle"><?php echo $formText_AddImages_Output;?></div>
<div class="popupform">
    <form class="output-content-form main" action="" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
    	<input type="hidden" name="cid" value="<?php echo $contentId;?>" >

        <?php
        $fwaFileuploadConfig = array (
          'module_folder' => 'People', // module id in which this block is used
          'id' => 'peoplebannerupload',
          'content_table' => 'contactperson',
          'content_field' => 'profileBannerImage',
          'content_module_id' => $moduleID, // id of module
          'dropZone' => 'block',
          'callback' => 'callbackOnImageUpload',
          'callbackDelete'=> 'callbackOnImageDelete',
      		'callbackAll' => 'callBackOnUploadAll',
      		'callbackStart' => 'callbackOnStart'

        );
          require __DIR__ . '/fileupload9/output.php';
        ?>
        <?php
        $field_ui_id = "upload_pagecover";
        ?>
    	<div class="popupformbtn"><input type="submit" class="saveFiles fw_button_color" name="sbmbtn" value="<?php echo $formText_save_output; ?>"></div>
        <div id="<?php echo $field_ui_id;?>_files">
            <?php
        	$account_path = ACCOUNT_PATH;
            $content_field = "profileBannerImage";
            $module_dir = $account_path . '/modules/UserProfile';
        	// Include module fields file and language variables
        	include($module_dir."/input/settings/fields/userprofilefields.php");

        	// Directory related functions from input
        	if(!function_exists("dirsizeexec")) include($module_dir."/input/fieldtypes/Image/fn_dirsizeexec.php");
        	if(!function_exists("mkdir_recursive")) include($module_dir."/input/fieldtypes/Image/fn_mkdir_recursive.php");

        	// Read fields in array
        	foreach($prefields as $s_field) {
        		$v_field = explode("¤",$s_field);
        		$v_fields[$v_field[0]] = $v_field;
        	}

            list($type, $resize_codes) = explode(":",strtolower($v_fields[$content_field][11]),2);
            list($fieldtype, $limit) = explode(',',$type);
            $image_count_limit = ($limit>0?$limit:1);
            if(!isset($resize_codes) or $resize_codes == '') $resize_codes = '0,0';
            $show_focuspoint = (strpos($resize_codes,"f")!==false ? true : false);
            $resize_codes = explode(":",$resize_codes);

            foreach($resize_codes as $key => $resize_code)
            { ?>
                <input type="hidden" class="handle handleInput handleInput<?php echo $key;?>" name="<?php echo $field_ui_id;?>_img[]" value=""/>
            <?php } ?>
        </div>
        <div class="modal fade" id="<?php echo $field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h4 class="modal-title"><?php echo $formText_DragTheCroppingAreaInRightPosition_imageCropping;?></h4>
                    </div>
                    <div class="modal-body">
                        <div id="<?php echo $field_ui_id;?>crop"><img /></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="<?php echo $field_ui_id;?>_reset"><?php echo $formText_Reset_fieldtype;?></button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $formText_save_input;?></button>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">

        var $<?php echo $field_ui_id;?>image = $('#<?php echo $field_ui_id;?>crop > img');

    	$("#<?php echo $field_ui_id;?>_reset").on('click', function(){
    		$<?php echo $field_ui_id;?>image.cropper('reset');
    	});
        $('#<?php echo $field_ui_id;?>modal').on('hidden.bs.modal', function () {
            var data = $<?php echo $field_ui_id;?>image.cropper('getData');
            var str = $(<?php echo $field_ui_id;?>handle).val() + obj_to_string_<?php echo $field_ui_id;?>(data);
            $(<?php echo $field_ui_id;?>handle).val(str);
            $<?php echo $field_ui_id;?>image.cropper('destroy');
            $(window).resize();
            imagesHandle.push( $('#<?php echo $field_ui_id;?>_files .handleInput').val());
            handle_<?php echo $field_ui_id;?>();
        });
        function <?php echo $field_ui_id;?>rempx(string){
            return Number(string.substring(0, (string.length - 2)));
        }

        function handle_<?php echo $field_ui_id;?>()
        {
            var handle = $('#<?php echo $field_ui_id;?>_files .handle'),
                handlefocus = $('#<?php echo $field_ui_id;?>_files .handlefocus');
            if(handle.length > 0)
            {
                <?php echo $field_ui_id;?>handle = $(handle).get(0);
                var option = $(<?php echo $field_ui_id;?>handle).val().split(':');
                var size = option[2].split(',');

                if(option[0] == 'process' && size[2] == 'c')
                {
                    $('#<?php echo $field_ui_id;?>modal').modal({show:true});
                    $<?php echo $field_ui_id;?>image.attr('src','../' + option[5] + '?caID=<?php echo $caID; ?>&uid=' + option[1] + '&_=' + Math.random()).cropper({
                        strict:false,
                        zoomable:false,
                        rotatable:false,
                        aspectRatio:size[0]/size[1],
                        autoCropArea:1,
                    });
                }
                $(<?php echo $field_ui_id;?>handle).removeClass("handle");
            }
            else if(handlefocus.length > 0)
            {
                <?php echo $field_ui_id;?>handle = $(handlefocus).get(0);
                $('#<?php echo $field_ui_id;?>focus').css({
                    'background-image': 'url('+$(<?php echo $field_ui_id;?>handle).data('src')+')',
                    width: $(<?php echo $field_ui_id;?>handle).data('w'),
                    height: $(<?php echo $field_ui_id;?>handle).data('h'),
                });
                $('#<?php echo $field_ui_id;?>focusmark').css({
                    left: $(<?php echo $field_ui_id;?>handle).data('x'),
                    top: $(<?php echo $field_ui_id;?>handle).data('y'),
                }).data('ratio',$(<?php echo $field_ui_id;?>handle).data('ratio'));
                $('#<?php echo $field_ui_id;?>modalfocus').modal({show:true});

                $(<?php echo $field_ui_id;?>handle).removeClass("handlefocus");
            }
        }
        function obj_to_string_<?php echo $field_ui_id;?>(obj)
        {
            var str = '';
            for (var p in obj) {
                if (obj.hasOwnProperty(p)) {
                    str += ':' + p + '|' + obj[p];
                }
            }
            return str;
        }
        </script>
    </form>
</div>
<style>
.modal-dialog {
    z-index: 1060;
}
.modal-body img {
  max-width: 100%;
}
</style>
<script type="text/javascript">

// New uploaded images to process
var imagesToProcess = [];
var imagesHandle = [];

function callbackOnImageUpload(data) {
	if(imagesToProcess.length == 0){
	    for (first in data.result) break;
	    var uploaded_image = data.result[first][0];
	    imagesToProcess.push(uploaded_image.upload_id);
        <?php foreach($resize_codes as $key => $resize_code) {?>
            $("#<?php echo $field_ui_id;?>_files .handleInput<?php echo $key;?>").addClass("handle");
            handle = $('#<?php echo $field_ui_id;?>_files .handle.handleInput<?php echo $key;?>').val("process:"+uploaded_image.upload_id+":<?php echo $resize_code;?>:"+uploaded_image.width+":"+uploaded_image.height+":"+uploaded_image.url+"");
        <?php } ?>
        handle_<?php echo $field_ui_id;?>();
	} else {
	}
}
// function callbackOnStart(){
// 	console.log(imagesToProcess);
// 	if(imagesToProcess.length > 0){
// 		// break;
// 	}
// }
function callbackOnImageDelete(data) {
	if(data != undefined && data.upload_id != undefined){
		var index = imagesToProcess.indexOf(data.upload_id);
		if (index > -1) {
	  		imagesToProcess.splice(index, 1);
    	  	imagesHandle.splice(index, 1);
            $("#<?php echo $field_ui_id;?>_files .handleInput").addClass("handle");
		}
	}
}

function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);
};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};

$(document).ready(function() {
    $('.output-content-form').on('submit', function(event) {
        event.preventDefault();
        var formdata = $(this).serializeArray();
        var data = {};
        $(formdata ).each(function(index, obj){
            data[obj.name] = obj.value;
        });
        data.imagesToProcess = imagesToProcess;
        data.imagesHandle = imagesHandle;

        ajaxCall('addImages', data, function(json) {
            if(json.error !== undefined)
            {
                $("#popup-image-message").html("<?php echo $formText_ErrorOccurredUploadingImage_Output;?>", true);
                $("#popup-image-message").show();
            } else {
                out_popup2.close();
                reloadPopup();
            }
        });
    });
});

</script>

<style>
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.popupeditform {
	border:none;
	border-top:1px dashed #e8e8e8;
}

.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.output-content-form input.popupforminput, .output-content-form textarea.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:5px;
}
textarea.popupforminput {
	min-height:200px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
}
.output-content-form input.error, .output-content-form textarea.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}

/**
 * Change image blocks
 */

.changeImage .changeImageUploadBlock {
    display:none;
}

.changeImageUploadBlock {
    padding:15px 0;
}
</style>
