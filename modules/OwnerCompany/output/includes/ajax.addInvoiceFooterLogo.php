<?php
$ownercompanyId = isset($_POST['ownercompanyId']) ? $o_main->db->escape_str($_POST['ownercompanyId']) : 0;

require_once __DIR__ . '/process_uploaded_image_to_entry.php';

/**
 * Update data on form submit
 */
if(isset($_POST['output_form_submit']))
{
    // process images
    if (isset($_POST['imagesToProcess'])) {
        foreach ($_POST['imagesToProcess'] as $imageToProcess) {
            process_uploaded_image_to_entry($o_main, $imageToProcess, $ownercompanyId);
        }
    }
}
?>

<div class="popupformTitle"><?php echo $formText_AddImages_Output;?></div>
<div class="popupform">
    <form class="output-content-form main" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCompanyPage";?>" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
    	<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>" >

        <?php
        $fwaFileuploadConfig = array (
          'module_folder' => 'OwnerCompany', // module id in which this block is used
          'id' => 'invoicelogofileupload',
          'content_table' => 'ownercompany',
          'content_field' => 'invoice_footer_logos',
          'content_module_id' => $moduleID, // id of FileArchiveGBox module
          'dropZone' => 'block',
          'callback' => 'callbackOnImageUpload',
          'callbackDelete'=> 'callbackOnImageDelete',
          'upload_type'=>'image'
        );
          require __DIR__ . '/fileupload8/output.php';
        ?>

    	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_save_output; ?>"></div>
    </form>
</div>

<script type="text/javascript">

// New uploaded images to process
var imagesToProcess = [];

function callbackOnImageUpload(data) {
    for (first in data.result) break;
    var uploaded_image = data.result[first][0];
    if(uploaded_image.error == undefined){
	    imagesToProcess.push(uploaded_image.upload_id);
    }
}
function callbackOnImageDelete(data) {
	if(data != undefined && data.upload_id != undefined){
		var index = imagesToProcess.indexOf(data.upload_id);
		if (index > -1) {
	  		imagesToProcess.splice(index, 1);
		}
	}
}

$(document).ready(function() {
    $('.output-content-form').on('submit', function(event) {
        event.preventDefault();
        var formdata = $(this).serializeArray();
        var data = {};
        $(formdata ).each(function(index, obj){
            data[obj.name] = obj.value;
        });
        data.imagesToProcess = imagesToProcess;
        ajaxCall('addInvoiceLogo', data, function(json) {
            document.location.reload();
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
