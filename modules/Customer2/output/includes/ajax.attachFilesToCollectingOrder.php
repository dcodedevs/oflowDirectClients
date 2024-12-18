<?php
$collectingorderId = $_POST['collectingorderId'] ? ($_POST['collectingorderId']) : 0;

$s_sql = "SELECT * FROM moduledata WHERE name = ?";
$o_query = $o_main->db->query($s_sql, array("Customer2"));
$module_data = $o_query ? $o_query->row_array() : array();
$customer2moduleID = $module_data['id'];
require_once __DIR__ . '/process_uploaded_image_to_entry.php';
/**
 * Update data on form submit
 */


 $fwaFileuploadConfig = array (
   'module_folder' => 'Customer2', // module id in which this block is used
   'id' => 'collectingorderfiles',
   'content_table' => 'customer_collectingorder',
   'content_field' => 'files_attached_to_invoice',
   'file_type'=>'pdf',
   'upload_type'=>'file',
   'content_module_id' => $customer2moduleID,
   'dropZone' => 'block',
   'callbackAll' => 'callBackOnUploadAll',
   'callbackStart' => 'callbackOnStart',
   'callbackDelete' => 'callbackOnDelete'

 );

if(isset($_POST['output_form_submit']))
{
    $fieldName = $fwaFileuploadConfig['id'];
    $fwaFileuploadConfig['content_id'] = $collectingorderId;
    include( __DIR__ . "/fileupload10/contentreg.php");

    $s_sql = "UPDATE customer_collectingorder SET moduleID = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($customer2moduleID, $collectingorderId));

    // // process images
    // if ($_POST['imagesToProcess']) {
    //     foreach ($_POST['imagesToProcess'] as $imageToProcess) {
    //         process_uploaded_image_to_entry($o_main, $imageToProcess, $collectingorderId);
    //     }
    // }
}
if($collectingorderId > 0)
{
	$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($collectingorderId));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
if($v_data) {
?>

<div class="popupformTitle"><?php echo $formText_AttachFilesToInvoice_output;?></div>
<div class="popupform">
    <form class="output-content-form main" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=attachFilesToCollectingOrder";?>" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
    	<input type="hidden" name="collectingorderId" value="<?php echo $collectingorderId;?>" >
        <?php
          require __DIR__ . '/fileupload10/output.php';
        ?>
    	<div class="popupformbtn"><input type="submit" class="saveFiles" name="sbmbtn" value="<?php echo $formText_save_output; ?>"></div>
    </form>
</div>

<script type="text/javascript">

function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){

}

// // Field info for tmp storage table
// var fileupload_data = {
//     content_module_id: '<?php echo $moduleID;?>',
//     content_table: 'customer_collectingorder',
//     content_field: 'files_attached_to_invoice',
//     fileupload_session_id: '',
//     folder_id: parseInt('<?php echo $_POST['folderId']; ?>'),
//     username: '<?php echo $variables->loggID; ?>',
//     modify_action: ''
// }
//
// // New uploaded images to process
// var imagesToProcess = [];
//
// function callbackOnImageUpload(data) {
//     for (first in data.result) break;
//     var uploaded_image = data.result[first][0];
//     imagesToProcess.push(uploaded_image.upload_id);
// }
// // function callbackOnStart(){
// // 	console.log(imagesToProcess);
// // 	if(imagesToProcess.length > 0){
// // 		// break;
// // 	}
// // }
// function callbackOnImageDelete(data) {
// 	if(data != undefined && data.upload_id != undefined){
// 		var index = imagesToProcess.indexOf(data.upload_id);
// 		if (index > -1) {
// 	  		imagesToProcess.splice(index, 1);
// 		}
// 	}
// }
// function callBackOnUploadAll(data) {
// 	fileupload_data.fileupload_session_id = data.fileupload_session_id;
//     $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);
// };
// function callbackOnStart(data) {
//     $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
// };

$(document).ready(function() {
    $('.output-content-form').on('submit', function(event) {
        event.preventDefault();
        var formdata = $(this).serializeArray();
        var data = {};
        $(formdata ).each(function(index, obj){
            data[obj.name] = obj.value;
        });
        // data.imagesToProcess = imagesToProcess;
        ajaxCall('attachFilesToCollectingOrder', data, function(json) {
            out_popup.addClass("close-reload");
            out_popup.close();
        });
    });
});

</script>

<style>
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
<?php } else { ?>

<?php } ?>
