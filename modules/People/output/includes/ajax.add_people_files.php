<?php
$peopleId = isset($_POST['cid']) ? ($_POST['cid']) : 0;

require_once __DIR__ . '/process_uploaded_image_to_entry.php';

/**
 * Update data on form submit
 */
if (isset($_POST['fileuploadaction']) && $_POST['fileuploadaction'] == 'delete') {
	if(isset($_POST['deletefileid']) && $_POST['deletefileid'] > 0) {
        $sql = "SELECT p.*
                 FROM people_files p
                WHERE p.id = ?";
        $o_query = $o_main->db->query($sql, array($_POST['deletefileid']));
        $peopleFiles = $o_query ? $o_query->row_array() : array();
        $files = json_decode($peopleFiles['file']);

		$sql = "DELETE FROM people_files WHERE id = ?";
		$o_query = $o_main->db->query($sql, array($_POST['deletefileid']));
        if($o_query) {
            foreach($files as $file){
                foreach($file[1] as $singleFile){
                    if($singleFile != ""){
                        if (is_file(__DIR__."/../../../../".$singleFile)) {
                            unlink(__DIR__."/../../../../".$singleFile);
                            rmdir(dirname(__DIR__."/../../../../".$singleFile));
                        }
                    }
                }
            }
        }
		// $peopleId - needed for sync script
		$peopleId = $peopleId;
		include("sync_people.php");

        return;
	}
}
if (isset($_POST['action']) && $_POST['action'] == 'visibleForCustomersCheck') {
	$fileId = isset($_POST['fileid']) ? ($_POST['fileid']) : 0;
	$checked = isset($_POST['checked']) ? ($_POST['checked']) : 0;
	if($fileId > 0) {
		if($checked){
			$sql = "UPDATE people_files SET visible_for_customers = 1 WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($fileId));
		} else {
			$sql = "UPDATE people_files SET visible_for_customers = 0 WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($fileId));
		}
	}
	return;
}
if (isset($_POST['action']) && $_POST['action'] == 'visibleForCurrentEmployeeCheck') {
	$fileId = isset($_POST['fileid']) ? ($_POST['fileid']) : 0;
	$checked = isset($_POST['checked']) ? ($_POST['checked']) : 0;
	if($fileId > 0) {
		if($checked){
			$sql = "UPDATE people_files SET visible_for_current_employee = 1 WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($fileId));
		} else {
			$sql = "UPDATE people_files SET visible_for_current_employee = 0 WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($fileId));
		}
	}
	return;
}
if (isset($_POST['action']) && $_POST['action'] == 'visibleForAllEmployeeCheck') {
	$fileId = isset($_POST['fileid']) ? ($_POST['fileid']) : 0;
	$checked = isset($_POST['checked']) ? ($_POST['checked']) : 0;
	if($fileId > 0) {
		if($checked){
			$sql = "UPDATE people_files SET visible_for_all_employee = 1 WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($fileId));
		} else {
			$sql = "UPDATE people_files SET visible_for_all_employee = 0 WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($fileId));
		}
	}
	return;
}

if(isset($_POST['output_form_submit']))
{
    // process images
    if (isset($_POST['imagesToProcess'])) {
        foreach ($_POST['imagesToProcess'] as $imageToProcess) {
	        $sql = "SELECT *
	                 FROM uploads
	                WHERE id = ?";
	        $o_query = $o_main->db->query($sql, array($imageToProcess));
	        $uploadData = $o_query ? $o_query->row_array() : array();
			$filename = $uploadData['filename'];

	        $s_sql = "INSERT INTO people_files SET
	        id=NULL,
	        moduleID = ?,
	        created = now(),
	        createdBy = ?,
			filename = ?,
	        peopleId = ?";
	        $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $filename, $peopleId));
	        $fileId = $o_main->db->insert_id();
			if($fileId > 0){
	            process_uploaded_image_to_entry($o_main, $imageToProcess, "", $fileId);
			}

			// $peopleId - needed for sync script
			$peopleId = $peopleId;
			include("sync_people.php");
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
    	<input type="hidden" name="cid" value="<?php echo $peopleId;?>" >

        <?php
        $fwaFileuploadConfig = array (
          'module_folder' => 'People', // module id in which this block is used
          'id' => 'peoplefileupload',
          'content_table' => 'people_files',
          'content_field' => 'file',
          'content_module_id' => $moduleID, // id of FileArchiveGBox module
          'dropZone' => 'block',
          'callback' => 'callbackOnImageUpload',
          'callbackDelete'=> 'callbackOnImageDelete',
		  'callbackAll' => 'callBackOnUploadAll',
		  'callbackStart' => 'callbackOnStart'

        );
          require __DIR__ . '/fileupload9/output.php';
        ?>

    	<div class="popupformbtn"><input type="submit" class="saveFiles" name="sbmbtn" value="<?php echo $formText_save_output; ?>"></div>
    </form>
</div>

<script type="text/javascript">
// New uploaded images to process
var imagesToProcess = [];

function callbackOnImageUpload(data) {
	if(imagesToProcess.length == 0){
	    for (first in data.result) break;
	    var uploaded_image = data.result[first][0];
	    imagesToProcess.push(uploaded_image.upload_id);
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
        ajaxCall('add_people_files', data, function(json) {
			fw_loading_start();
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
