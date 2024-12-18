<?php
$s_sql = "SELECT * FROM moduledata WHERE name = 'Customer2'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();

$fwaFileuploadConfigs = array(
	array (
		'module_folder' => 'Customer2', // module id in which this block is used
		'id' => 'customer_member_link_settingslogo',
		'upload_type'=>'image',
		'content_table' => 'customer_member_link_settings',
		'content_field' => 'logo',
		'content_id' => 1,
		'content_module_id' => $module_data['uniqueID'], // id of module
		'dropZone' => 'block',
		'callback' => 'callbackOnFileUpload',
		'callbackAll' => 'callBackOnUploadAll',
		'callbackStart' => 'callbackOnStart'
	),
);

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$s_sql = "SELECT * FROM customer_member_link_settings WHERE id = 1";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows() > 0)
		{
			$s_sql = "UPDATE customer_member_link_settings SET
			updated = NOW(),
			updatedBy = ?,
			top_text = ?,
			bottom_text = ?
			WHERE id = 1";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['top_text'], $_POST['bottom_text']));
		} else {
			$s_sql = "INSERT INTO customer_member_link_settings SET
			id = 1,
			moduleID = ?,
			created = NOW(),
			createdBy = ?,
			top_text = ?,
			bottom_text = ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['top_text'], $_POST['bottom_text']));
		}
		
		foreach($fwaFileuploadConfigs as $fwaFileuploadConfig)
		{
			$fieldName = $fwaFileuploadConfig['id'];
			$fwaFileuploadConfig['content_id'] = 1;
			include(__DIR__."/fileupload_popup/contentreg.php");
		}
		
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&inc_obj=member_list&customerId=".$_POST['customerId'];
		return;
	}
}

$v_data = array();
$s_sql = "SELECT * FROM customer_member_link_settings WHERE id = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$v_data = $o_query->row_array();
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act;?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">

	<div class="inner">
		<div class="popupformTitle"><?php echo $formText_EditTextInEmail_Output;?></div>
		
		<div class="line">
			<div class="lineTitle"><?php echo $formText_TopText_Output; ?></div>
			<div class="lineInput"><textarea class="popupforminput" name="top_text"><?php echo $v_data['top_text'];?></textarea></div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_BottomText_Output; ?></div>
			<div class="lineInput"><textarea class="popupforminput" name="bottom_text"><?php echo $v_data['bottom_text'];?></textarea></div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_Logo_Output; ?></div>
			<div class="lineInput"><?php
			$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
			include __DIR__ . '/fileupload_popup/output.php';
			?></div>
			<div class="clear"></div>
		</div>

	</div>
	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					fw_loading_end();
					/*if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {*/
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					//}
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
function updatePreview(){
	$(".fwaFileupload_FilesList_Filesarticleimageeuploadpopup li").each(function(){
		if(!$(this).hasClass("deleted")){
		}
	})
}
function callbackOnFileUpload(data) {
};
function callBackOnUploadAll(data) {
	updatePreview();
};
function callbackOnStart(data) {
};
function callbackOnDelete(data){
	updatePreview();
}
</script>
