<?php
$action = $_POST['action'];

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

if($action == "updateShowToCustomer" && $_POST['cid']) {
    $s_sql = "UPDATE subscriptionmulti_files SET updated = NOW(), updatedBy = ?, show_to_customer = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['selected'], $_POST['cid']));
    return;
}
if($action == "updateShowToPerformer" && $_POST['cid']) {
    $s_sql = "UPDATE subscriptionmulti_files SET updated = NOW(), updatedBy = ?, show_to_performer = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['selected'], $_POST['cid']));
    return;
}

$current_content_id = ($_POST['cid']);
$fileId = $_POST['fileId'];
// Get customer id
$s_sql = "SELECT c.id customerId FROM customer c LEFT JOIN subscriptionmulti s ON s.customerId = c.id WHERE s.id = ?";
$o_query = $o_main->db->query($s_sql, array($current_content_id));
if($o_query && $o_query->num_rows()>0) {
    $customerData = $o_query->row_array();
	$customerId = $customerData['customerId'];
}

require_once __DIR__ . '/process_uploaded_image_to_entry.php';
$list_filter_main = $_POST['list_filter_main'] ? $_POST['list_filter_main'] : "";
/**
 * Update data on form submit
 */
if(isset($_POST['output_form_submit']))
{
    if($fileId > 0){
        $s_sql = "UPDATE subscriptionmulti_files SET updated = NOW(), updatedBy = ? WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($variables->loggID, $fileId));
    }
    // process images
    if ($_POST['imagesToProcess']) {
        foreach ($_POST['imagesToProcess'] as $imageToProcess) {
            $s_sql = "INSERT INTO subscriptionmulti_files SET created = NOW(), createdBy = ?, subscriptionmulti_id = ?, show_to_customer = ?, show_to_performer = ?";
    		$o_query = $o_main->db->query($s_sql, array($variables->loggID, $current_content_id, $_POST['show_to_customer'], $_POST['show_to_performer']));
            var_dump($o_query);
            if($o_query){
                $projectFileNewId = $o_main->db->insert_id();
            }
            process_uploaded_image_to_entry($o_main, $imageToProcess, $projectFileNewId);
        }
    }
}

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;

// Create & check folders

$content_table = 'subscriptionmulti';

?>

<div class="popupformTitle"><?php echo $formText_AddFile_output;?></div>
<div class="popupform">
<div id="popup-validate-message" style="display:none;"></div>
<form class="output-homes-form main" action="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=addSubscriptionFileNew";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
    <input type="hidden" name="fileId" value="<?php echo $fileId;?>" >
	<input type="hidden" name="cid" value="<?php echo $_POST['cid'];?>">
    <div class="inner">
        <?php if($projectFileId == 0) {?>
            <?php
            $fwaFileuploadConfig = array (
              'module_folder' => 'Customer2', // module id in which this block is used
              'id' => 'subscriptionmultifileupload',
              'content_table' => 'subscriptionmulti_files',
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
        <?php } ?>
        <?php /*
        <br/>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_ShowToCustomer_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace checkbox" name="show_to_customer" type="checkbox" value="1" autocomplete="off"></div>
		<div class="clear"></div>
		</div>
        <?php if($customer_basisconfig['activate_files_visible_for_performer']) {?>
    		<div class="line">
    		<div class="lineTitle"><?php echo $formText_ShowToPerformer_Output; ?></div>
    		<div class="lineInput"><input class="popupforminput botspace checkbox" name="show_to_performer" type="checkbox" value="1" autocomplete="off"></div>
    		<div class="clear"></div>
    		</div>
        <?php } ?>
        */?>
        <div class="clear"></div>
    </div>
    <div class="popupformbtn">
    	<a href="#" class="b-close cancelBtn"><?php echo $formText_Cancel_output; ?></a>
    	<input type="submit" class="saveFiles" name="sbmbtn" value="<?php echo $formText_save_output; ?>">
    </div>
</form>
</div>

<div class="clear"></div>

<script type="text/javascript">

// Field info for tmp storage table
var fileupload_data = {
    content_module_id: '<?php echo $moduleID;?>',
    content_table: 'project2_files',
    content_field: 'file',
    fileupload_session_id: '',
    folder_id: parseInt('<?php echo $_POST['folderId']; ?>'),
    username: '<?php echo $variables->loggID; ?>',
    modify_action: '',
    s_group_id: '<?php echo $s_group_id?>'
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
    $('.popupformbtn .saveFiles').val('<?php echo $formText_save_output; ?>').prop('disabled',false);
};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};

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

    $(document).ready(function() {
        $('.output-homes-form').on('submit', function(event) {
            event.preventDefault();
            var formdata = $(this).serializeArray();
            var data = {};
            $(formdata ).each(function(index, obj){
                data[obj.name] = obj.value;
            });
            data.imagesToProcess = imagesToProcess;
            ajaxCall('addSubscriptionFileNew', data, function(json) {
                reloadPage();
            });
        });
    });
});

</script>

<style media="screen">
.popupeditbox .popupform, .popupeditform {
    border: 0;
}
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
