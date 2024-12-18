<?php
$o_query = $o_main->db->query("SELECT * FROM subscriptionmulti WHERE id = '".$o_main->db->escape_str($_POST['subscribtionId'])."'");
$v_collectingorder = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['subscribtionId']) && $_POST['subscribtionId'] > 0)
		{
			if($_POST['agreement_terminated_date'] != ""){
				if($v_collectingorder['stoppedDate'] == "0000-00-00" || $v_collectingorder['stoppedDate'] == ""){
					$fw_error_msg[] = $formText_AgreementTerminatedDateCanNotBeSetWhileStoppedDateIsNotSet_output;
					return;
				}
				if(strtotime($_POST['agreement_terminated_date']) < strtotime($v_collectingorder['stoppedDate'])) {
					$fw_error_msg[] = $formText_AgreementTerminatedDateCanNotBeSetEarlierThanStoppedDate_output;
					return;
				}
			}
			$s_sql = "UPDATE subscriptionmulti SET
			updated = NOW(),
			updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
			agreement_entered_date = '".$o_main->db->escape_str(!empty($_POST['agreement_entered_date']) ? date("Y-m-d", strtotime($_POST['agreement_entered_date'])) : '')."',
			agreement_terminated_date = '".$o_main->db->escape_str(!empty($_POST['agreement_terminated_date']) ? date("Y-m-d", strtotime($_POST['agreement_terminated_date'])) : '')."'
			WHERE id = '".$o_main->db->escape_str($_POST['subscribtionId'])."'";
			$o_main->db->query($s_sql);
		} else {
			$fw_error_msg[] = $formText_MissingSubscription_output;
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_collectingorder['customerId'];
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscriptionAgreementDates";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="subscribtionId" value="<?php print $_POST['subscribtionId'];?>">

	<div class="inner">
		<div class="line">
			<div class="lineTitle"><?php echo $formText_AgreementEnteredDate_output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput botspace datepicker" autocomplete="off" name="agreement_entered_date" value="<?php echo (!empty($v_collectingorder['agreement_entered_date']) && $v_collectingorder['agreement_entered_date']!="0000-00-00"  ? date("d.m.Y", strtotime($v_collectingorder['agreement_entered_date'])) : '');?>" ></div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_AgreementTerminatedDate_output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput botspace datepicker" autocomplete="off" name="agreement_terminated_date" value="<?php echo (!empty($v_collectingorder['agreement_terminated_date']) && $v_collectingorder['agreement_terminated_date']!="0000-00-00" ? date("d.m.Y", strtotime($v_collectingorder['agreement_terminated_date'])) : '');?>" ></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
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

			$("#popup-validate-message").hide()
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
