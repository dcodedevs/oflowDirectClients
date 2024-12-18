<?php 
$l_creditor_id = isset($_POST['creditor_id']) ? $_POST['creditor_id'] : 0;
if($_POST['output_form_submit']){
	if($l_creditor_id > 0){
		$handled = 0;
		if($_POST['handled']){
			$handled = 1;
		}
		$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_handled = ?, creditor_marked_ceases_to_exist_note = ?, creditor_marked_ceases_to_exist_handled_date = NOW(), creditor_marked_ceases_to_exist_handled_by = ? WHERE id = ?", array($handled, $_POST['note'], $variables->loggID,$l_creditor_id));
	}
	$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_collectingorder['projectId'];
	return;
}
$s_sql = "SELECT creditor.*
FROM creditor
WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($l_creditor_id));
$v_creditor = ($o_query ? $o_query->row_array() : array());
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=mark_as_handled";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="creditor_id" value="<?php print $l_creditor_id;?>">

		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CreditorName_output; ?></div>
				<div class="lineInput">
					<?php echo $v_creditor['companyname'];?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CreditorMarkedCeasesToExistDate_output; ?></div>
				<div class="lineInput">
					<?php echo date("d.m.Y", strtotime($v_creditor['creditor_marked_ceases_to_exist_date']));?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Reason_output; ?></div>
				<div class="lineInput">
					<?php echo $v_creditor['creditor_marked_ceases_to_exist_reason'];?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Handled_LID6009; ?></div>
				<div class="lineInput">
					<input type="checkbox" name="handled" value="1" <?php if($v_creditor['creditor_marked_ceases_to_exist_handled']) echo 'checked';?>/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Note_LID6009; ?></div>
				<div class="lineInput">
					<textarea name="note" class="popupforminput botspace"><?php echo $v_creditor['creditor_marked_ceases_to_exist_note'];?></textarea>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_LID4371;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_output; ?>">
		</div>
	</form>
</div>
<style>

.popupform label.error {
display: none !important;
}
.popupform .lineTitle {
font-weight:700;
margin-bottom: 10px;
}
.popupform textarea.popupforminput {
border-radius: 4px;
padding:5px 10px;
font-size:12px;
line-height:17px;
}
</style>
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
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_LID3769;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});
	},
	invalidHandler: function(event, validator) {
		var errors = validator.numberOfInvalids();
		if (errors) {
			var message = errors == 1
			? '<?php echo $formText_YouMissed_LID3863; ?> 1 <?php echo $formText_field_LID3962; ?>. <?php echo $formText_TheyHaveBeenHighlighted_LID4061; ?>'
			: '<?php echo $formText_YouMissed_LID7918; ?> ' + errors + ' <?php echo $formText_fields_LID4160; ?>. <?php echo $formText_TheyHaveBeenHighlighted_LID8017; ?>';

			$("#popup-validate-message").html(message);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
		} else {
			$("#popup-validate-message").hide();
		}
		setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
	}
});
$(".datepicker").datepicker({
	firstDay: 1,
	dateFormat: 'dd.mm.yy',
	showButtonPanel: true,
	 closeText: 'Clear',
	 onClose: function (dateText, inst) {
		var event = arguments.callee.caller.caller.arguments[0];
		// If "Clear" gets clicked, then really clear it
		if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
			$(this).val('');
		}
	 }
});
});
</script>