<div class="popupformTitle"><?php echo $formText_AddFile_output;?></div>
<form class="output-form output-form-files" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_files";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php echo $_POST['cid'];?>">
  <?php
    $s_sql = "SELECT * FROM moduledata WHERE name = 'FileArchiveGBox'";
    $o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
	    $fileArchiveModuleId = $o_query->row_array();
		$fileArchiveModuleId = $fileArchiveModuleId['uniqueID'];
	}
    $fwaFileuploadConfig = array (
      'module_folder' => 'FileArchiveGBox',
      'id' => 'employeefileupload',
      'content_table' => 'sys_filearchive_file_version',
      'content_field' => 'file',
      'content_module_id' => $fileArchiveModuleId,
      'dropZone' => 'block',
        'callbackAll' => 'callbackOnFileUpload',
        'callbackStart' => 'callbackOnFileUploadStart'
    );
    require __DIR__ . '/fileupload9/output.php';
  ?>
  	<ul class="fileuploadPopup_Errors"></ul>
	<div class="popupformbtn">
		<a href="#" class="b-close cancelBtn"><?php echo $formText_Cancel_output; ?></a>
		<input type="submit" class="savefilesbtn" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
	</div>
</form>

<?php
if(isset($_POST['cid'])){ $current_content_id = ($_POST['cid']); } else { $current_content_id = NULL; }

// Create & check folders
$f_check_sql = "SELECT * FROM employee WHERE id = ".$current_content_id." ORDER BY name";
require_once __DIR__ . '/filearchive_functions.php';
check_filearchive_folder($o_main, 'People', $f_check_sql, 'people', 'name');
create_subscription_folders($o_main, $current_content_id);

$content_table = 'people';
$s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
$o_query = $o_main->db->query($s_sql, array($content_table, $current_content_id));
if($o_query && $o_query->num_rows()>0) {
    $folder_data = $o_query->row_array();
	$folder_id = $folder_data['id'];
}
?>

<script>
var imagesToProcess = [];
var fileupload_data = {
	content_module_id: '<?php echo $fwaFileuploadConfig['content_module_id'];?>',
	content_table: '<?php echo $fwaFileuploadConfig['content_table'];?>',
	content_field: '<?php echo $fwaFileuploadConfig['content_field'];?>',
	fileupload_session_id: '',
	folder_id: '<?php echo $folder_id; ?>',
	username: '<?php echo $variables->loggID; ?>',
	modify_action: ''
}

function callbackOnFileUploadStart(data) {
	$('[name="sbmbtn"]').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled', true);
}

function callbackOnFileUpload(data) {
	fileupload_data.fileupload_session_id = data.fileupload_session_id;
	$('[name="sbmbtn"]').val('<?php echo $formText_save_output;?>').prop('disabled', false);
}

// Reload page function
function reloadPage() {
	var redirect_url = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['cid']; ?>';
	window.location = redirect_url;
	$('#popupeditbox .b-close').unbind('click',reloadPage);
}

$(document).ready(function() {
	var form = $('.output-form-files');
	form.on('submit', function(e) {
		e.preventDefault();

		$.ajax({
			url: '../modules/<?php echo $fwaFileuploadConfig['module_folder']; ?>/output/ajax.saveFiles.php',
			type: "POST",
			dataType: "json",
			data: fileupload_data,
			success: function(json){
				// Save errors
				if (json.errors.length > 0) {
					// Build error block
					var errorMessage;
					json.errors.forEach(function(item,index) {
						if (item.type == 'fileexists') errorMessage = '<?php echo $formText_FileAlreadyExists_output; ?>';
						$('.fileuploadPopup_Errors').append('<li>' + item.filename + ' - ' + errorMessage+ '</li>');
					});
					// Show error, hide everything else
					$('.fileuploadPopup_Errors').show();
					$('.fwaFileupload').hide();
					$('[name="sbmbtn"]').hide();
					// We will reload page when popup is closed
					$('#popupeditbox .b-close').on('click',reloadPage);
				}
				// No errors
				else {
					reloadPage();
				}

			}
		});
	});

	$('.cancelBtn').on('click', function(event) {
		event.preventDefault();
		fileupload_data.modify_action = 'delete_unsaved';
		$.ajax({
			url: '../modules/<?php echo $fwaFileuploadConfig['module_folder']; ?>/output/includes/ajax.modifyFiles.php',
			type: "POST",
			dataType:"json",
			data: fileupload_data,
			success: function(json){
				//
			}
		});
	});
});
</script>

<style media="screen">

.fileuploadPopup_Errors {
	display:none;
	margin:0;
	padding:20px 0px;
	color:red;
	font-size:13px;
}
.fileuploadPopup_Errors li {
	padding:5px 0;
}

#popupeditbox .button.b-close {
	display:none;
}

.cancelBtn {
	display:inline-block;
	font-size:14px;
	margin-right:10px;
}

input[name="sbmbtn"]:disabled {
    background: #B0B5B9;
}

</style>
