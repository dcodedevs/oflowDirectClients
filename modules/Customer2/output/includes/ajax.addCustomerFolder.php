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

$current_content_id = ($_POST['cid']);
$parent_id = $_POST['parent_id'];
$customer_id = $_POST['customer_id'];
$subunit_id = $_POST['subunit_id'];

/**
 * Update data on form submit
 */
if(isset($_POST['output_form_submit']))
{
    if($_POST['name'] != "" && $_POST['customer_id'] != ""){
        if(isset($_POST['cid']) && $_POST['cid'] > 0)
        {
            $s_sql = "SELECT * FROM customer_folders WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
            if($o_query && $o_query->num_rows() == 1) {
                $s_sql = "UPDATE customer_folders SET
                updated = now(),
                updatedBy= ?,
                name= ?,
                parent_id = ?,
				subunit_id = ?,
                customer_id = ?
                WHERE id = ?";
                $o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['parent_id'], $_POST['subunit_id'], $_POST['customer_id'],$_POST['cid']));
            }
        } else {
            $s_sql = "INSERT INTO customer_folders SET
            id=NULL,
            moduleID = ?,
            created = now(),
            createdBy= ?,
            name = ?,
            parent_id = ?,
			subunit_id = ?,
            customer_id = ?";
            $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['name'], $_POST['parent_id'], $_POST['subunit_id'], $_POST['customer_id']));
        }

        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
        return;
    } else {
        $fw_error_msg[] = $formText_MissingFields_output;
    }
}

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$current_content_id;


if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM customer_folders WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	$v_data = $o_query ? $o_query->row_array() : array();
}

if(isset($_POST['parent_id']) && $_POST['parent_id'] > 0)
{
	$s_sql = "SELECT * FROM customer_folders WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['parent_id']));
	$parent_data = $o_query ? $o_query->row_array() : array();
}
?>

<div class="popupformTitle"><?php echo $formText_AddFolder_output;?></div>
<div class="popupform">
<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form main" action="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=addCustomerFolder";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
    <input type="hidden" name="parent_id" value="<?php echo $parent_id;?>" >
    <input type="hidden" name="subunit_id" value="<?php echo $subunit_id;?>" >
    <input type="hidden" name="customer_id" value="<?php echo $customer_id;?>" >
	<input type="hidden" name="cid" value="<?php echo $_POST['cid'];?>">
    <div class="inner">
        <?php if($parent_data) { ?>
            <div class="line">
                <?php echo $formText_ParentFolder_output.": ".$parent_data['name'];?>
            </div>
        <?php } ?>
        <div class="line">
        <div class="lineTitle"><?php echo $formText_FolderName_Output; ?></div>
        <div class="lineInput">
            <input type="text" name="name" value="<?php echo $v_data['name'];?>" class="popupforminput botspace" required autocomplete="off"/>
        </div>
        <div class="clear"></div>
        </div>
    </div>
    <div class="popupformbtn">
    	<a href="#" class="b-close cancelBtn"><?php echo $formText_Cancel_output; ?></a>
    	<input type="submit" class="saveFiles" name="sbmbtn" value="<?php echo $formText_save_output; ?>">
    </div>
</form>
</div>

<div class="clear"></div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

// Field info for tmp storage table
var fileupload_data = {
    content_module_id: '<?php echo $moduleID;?>',
    content_table: 'customer_files',
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
        $("form.output-form").validate({
    		submitHandler: function(form) {
    			fw_loading_start();
                $("#popup-validate-message").html("");
    			$.ajax({
    				url: $(form).attr("action"),
    				cache: false,
    				type: "POST",
    				dataType: "json",
    				data: $(form).serialize(),
    				success: function (data) {
    					fw_loading_end();
    					if(data.error !== undefined)
    					{
    						$.each(data.error, function(index, value){
    							var _type = Array("error");
    							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
    							$("#popup-validate-message").append(value);
    						});
    						$("#popup-validate-message").show();
    						fw_loading_end();
    						fw_click_instance = fw_changes_made = false;
    					} else {
    						if(data.redirect_url !== undefined)
    						{
    							out_popup.addClass("close-reload");
    							out_popup.close();
    						}
    					}
    				}
    			}).fail(function() {
    				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
    				$("#popup-validate-message").show();
    				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
    				fw_loading_end();
    			});
    		},
    		invalidHandler: function(event, validator) {
    			var errors = validator.numberOfInvalids();
    			if (errors) {
    				var message = errors == 1
    				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
    				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

    				$("#popup-validate-message").html(message);
    				$("#popup-validate-message").show();
    				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
    			} else {
    				$("#popup-validate-message").hide();
    			}
    			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
    		}
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
.popupeditbox label.error {
    display: none !important;
}

</style>
