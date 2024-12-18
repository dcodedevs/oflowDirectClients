<?php
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;

$s_sql = "SELECT * FROM moduledata WHERE name = ?";
$o_query = $o_main->db->query($s_sql, array("Customer2"));
$module_data = $o_query ? $o_query->row_array() : array();
$customer2moduleID = $module_data['id'];
require_once __DIR__ . '/process_uploaded_image_to_entry.php';
/**
 * Update data on form submit
 */
if(isset($_POST['output_form_submit']))
{
    // process images
    if ($_POST['imagesToProcess']) {

        $s_sql = "SELECT * FROM offer_attached_files";
        $o_query = $o_main->db->query($s_sql);
        $customer_file = $o_query ? $o_query->row_array() : array();
        if(!$customer_file){
            $s_sql = "INSERT INTO offer_attached_files SET created = NOW()";
            $o_query = $o_main->db->query($s_sql);
            $offer_attached_files_id = $o_main->db->insert_id();
        } else {
            $offer_attached_files_id = $customer_file['id'];
        }

        foreach ($_POST['imagesToProcess'] as $imageToProcess) {
            process_uploaded_image_to_entry($o_main, $imageToProcess, $offer_attached_files_id);
        }
    }
}
    $s_sql = "SELECT offer_attached_files.* FROM offer_attached_files ORDER BY id";
    $o_query = $o_main->db->query($s_sql);
    $offerFiles = ($o_query ? $o_query->row_array() : array());
    $attachedFiles = json_decode($offerFiles['file'], true);
?>

<div class="popupformTitle"><?php echo $formText_AttachFilesToEmailForAllOffers_output;?></div>
<div class="popupform">
    <form class="output-content-form main" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=attachFilesToOffer";?>" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
        <div class="attachedFiles">
            <table style="width: 100%; table-layout: fixed;">
            <?php


            foreach($attachedFiles as $file){
                $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=offer_attached_files&field=file&ID='.$offerFiles['id'];

                ?>
                    <tr>
                        <td style="padding: 0;" width="90%"><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></td>
                        <td style="padding: 0 10px;" width="10%" style="text-align: right;">
                            <?php if($moduleAccesslevel > 110) { ?>
                            <button class="output-btn small output-delete-attachedfile-offer-all editBtnIcon" data-offer-id="<?php echo $offerFiles['id']; ?>" data-uid="<?php echo $file[4];?>">
                                <span class="glyphicon glyphicon-trash"></span>
                            <?php } ?>

                        </td>
                    </tr>
                <?php
            }
            ?>
            </table>
        </div>
        <?php

        $fwaFileuploadConfig = array (
          'module_folder' => 'Customer2', // module id in which this block is used
          'id' => 'collectingorderfiles',
          'content_table' => 'offer_attached_files',
          'content_field' => 'file',
          'content_module_id' => $customer2moduleID,
          'dropZone' => 'block',
          'callback' => 'callbackOnImageUpload',
          'callbackDelete'=> 'callbackOnImageDelete',
		  'callbackAll' => 'callBackOnUploadAll',
	      'callbackStart' => 'callbackOnStart'

        );
          require __DIR__ . '/fileupload9/output.php';
        ?>
    	<div class="popupformbtn"><input type="submit" class="saveFiles" name="sbmbtn" value="<?php echo $formText_UploadNew_output; ?>"></div>
    </form>
</div>

<script type="text/javascript">

// Field info for tmp storage table
var fileupload_data = {
    content_module_id: '<?php echo $moduleID;?>',
    content_table: 'customer_collectingorder',
    content_field: 'files_attached_to_invoice',
    fileupload_session_id: '',
    folder_id: parseInt('<?php echo $_POST['folderId']; ?>'),
    username: '<?php echo $variables->loggID; ?>',
    modify_action: ''
}

// New uploaded images to process
var imagesToProcess = [];

function callbackOnImageUpload(data) {
    for (first in data.result) break;
    var uploaded_image = data.result[first][0];
    imagesToProcess.push(uploaded_image.upload_id);
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
		}
	}
}
function callBackOnUploadAll(data) {
	fileupload_data.fileupload_session_id = data.fileupload_session_id;
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
        ajaxCall('attachFilesToOfferAll', data, function(json) {
            out_popup.addClass("close-reload");
            out_popup.close();
        });
    });
    $(".output-delete-attachedfile-offer-all").off("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            uid: $(this).data('uid'),
            offerId: $(this).data("offer-id"),
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteAttachedFile_output; ?>', function(result) {
            if (result) {
                ajaxCall('deleteAttachedFileOfferAll', data, function(json) {
                    e.preventDefault();
                    var data = {
                    };
                    ajaxCall('attachFilesToOfferAll', data, function(json) {
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                    });
                });
            }
        }).css({"z-index": 10000});
    });
});

</script>

<style>
.popupform .attachedFiles {
    margin-bottom: 20px;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:0;
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
.popupformbtn .saveFiles:disabled {
    background: #B0B5B9;
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
