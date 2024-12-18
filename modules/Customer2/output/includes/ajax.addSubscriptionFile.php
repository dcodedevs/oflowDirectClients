<?php
$current_content_id = ($_POST['cid']);

// Get customer id
$s_sql = "SELECT c.id customerId FROM customer c LEFT JOIN subscriptionmulti s ON s.customerId = c.id WHERE s.id = ?";
$o_query = $o_main->db->query($s_sql, array($current_content_id));
if($o_query && $o_query->num_rows()>0) {
    $customerData = $o_query->row_array();
	$customerId = $customerData['customerId'];
}

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;

// Create & check folders
require_once __DIR__ . '/filearchive_functions.php';
// create_customer_folders($o_main, $customerId);

$content_table = 'subscriptionmulti';

$s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
$o_query = $o_main->db->query($s_sql, array($content_table, $current_content_id));
if($o_query && $o_query->num_rows()>0) {
    $folder_data = $o_query->row_array();
	$folder_id = $folder_data['id'];
}

$o_query = $o_main->db->get_where('sys_filearchive_folder', array(
    'parent_id' => $folder_id,
));

$subfolders = $o_query ? $o_query->result_array() : array();
?>

<div class="popupformTitle"><?php echo $formText_AddFile_output;?></div>
<div class="popupform">
<div id="popup-validate-message" style="display:none;"></div>
<form class="output-homes-form main" action="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_case";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php echo $_POST['cid'];?>">
    <div class="inner">
        <div class="line" style="display: none;">
        <div class="lineTitle"><?php echo $formText_ChooseFolder_Output; ?></div>
        <div class="lineInput">
            <select class="selectedSubfolderId popupforminput botspace" name="folder_id">
                <?php foreach ($subfolders as $subfolder): ?>
                    <option value="<?php echo $subfolder['id']; ?>" <?php if(trim($subfolder['name']) == 'Vises kun her' && $_POST['visible'] == "visible_here") echo 'selected';?>><?php echo $subfolder['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="clear"></div>
        </div>

        <?php
        /**
        * Fileupload block (Framework asset)
        * Config array should be defined before fileupload asset is required!
        * Check includes/fileupload/readme.md for more info
        */
        $s_sql = "SELECT * FROM moduledata WHERE name = 'FileArchiveGBox'";
        $o_query = $o_main->db->query($s_sql);
        if($o_query && $o_query->num_rows()>0) {
            $fileArchiveModuleId = $o_query->row_array();
            $fileArchiveModuleId = $fileArchiveModuleId['uniqueID'];
        }

        $fwaFileuploadConfig = array (
          'module_folder' => 'FileArchiveGBox',
          'id' => 'casefileupload',
          'content_table' => 'sys_filearchive_file_version',
          'content_field' => 'file',
          'content_module_id' => $fileArchiveModuleId,
          'dropZone' => 'block',
          'callback' => 'callbackOnFileUpload',
          'callbackStart' => 'callbackOnFileUploadStart',
          'callbackAll' => 'callbackOnFileUploadAll',
          'callbackDelete' => 'callbackOnFileDelete'
        );
        // Initialize by requiring fileupload block
        if($_POST['cid'] > 0) {
            require __DIR__ . '/fileupload7/output.php';
        }
        ?>
        <div class="clear"></div>
    </div>
</form>
</div>

<ul class="fileuploadPopup_Errors"></ul>

<div class="popupformbtn">
	<a href="#" class="b-close cancelBtn"><?php echo $formText_Cancel_output; ?></a>
	<input type="submit" name="sbmbtn" value="<?php echo $formText_save_output; ?>">
</div>
<div class="clear"></div>

<script type="text/javascript">

var fileupload_data = {
	content_module_id: '<?php echo $fwaFileuploadConfig['content_module_id'];?>',
	content_table: '<?php echo $fwaFileuploadConfig['content_table'];?>',
	content_field: '<?php echo $fwaFileuploadConfig['content_field'];?>',
	fileupload_session_id: '',
	username: '<?php echo $_GET['userID']; ?>',
	modify_action: ''
}

function callbackOnFileUploadStart(data) {
	$('[name="sbmbtn"]').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled', true);
}

function callbackOnFileUpload(data) {
	fileupload_data.fileupload_session_id = data.fileupload_session_id;
}

function callbackOnFileUploadAll(data) {
	$('[name="sbmbtn"]').val('<?php echo $formText_save_output;?>').prop('disabled', false);
}

function callbackOnFileDelete(data) {

}

$(function() {

	// Reload page function
	function reloadPage() {

        var subscriptions = $(".p_contentBlock .subscription-block");
        var openedSubscriptions = "";
        if(subscriptions.length > 0){
            openedSubscriptions = "&openedSubscriptions=";
            subscriptions.each(function(){
                if($(this).find(".subscription-block-dropdown").is(":visible")){
                    openedSubscriptions += $(this).find(".subscription-block-title").data("subscription-id")+",";
                }
            })
        }
        fw_load_ajax('<?php echo $s_page_reload_url;?>'+openedSubscriptions, '', false);
		// location.reload();
		$('#popupeditbox .b-close').unbind('click',reloadPage);
        $('#popupeditbox .b-close').click();
	}

	$('.cancelBtn').on('click', function(event) {
        // TODO delete unsaved files on close needs to be fixed
		// event.preventDefault();
		// fileupload_data.modify_action = 'delete_unsaved';
		// $.ajax({
		// 	url: '../modules/<?php echo $fwaFileuploadConfig['module_folder']; ?>/output/includes/ajax.modifyFiles.php',
		// 	type: "POST",
		// 	dataType:"json",
		// 	data: fileupload_data,
		// 	success: function(json){
		// 		//
		// 	}
		// });
	});

	$('#popupeditboxcontent .popupformbtn input[name=sbmbtn]').on('click',function(e){
    	fw_loading_start();
        fileupload_data.folder_id = $('.selectedSubfolderId').val();

    	$.ajax({
    		url: '../modules/<?php echo $fwaFileuploadConfig['module_folder']; ?>/output/ajax.saveFiles.php',
    		type: "POST",
    		dataType:"json",
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
    				$('.folderSelect').hide();
    				$('[name="sbmbtn"]').hide();
    				$('.popupformname').hide();
    				// We will reload page when popup is closed
    				$('#popupeditbox .b-close').on('click',reloadPage);
    				fw_loading_end();
    			}
    			// No errors
    			else {
    				reloadPage();
    			}
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
