<?php
$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['report_id']));
$v_data = $o_query && $o_query->num_rows()>0 ? $o_query->row_array() : array();

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$s_sql = "UPDATE collecting_cases_report_24so SET
		updated = now(),
		updatedBy= ?,
        fees_forgiven_amount='".$o_main->db->escape_str($_POST['fees_forgiven_amount'])."',
        sent_without_fees_amount='".$o_main->db->escape_str($_POST['sent_without_fees_amount'])."',
        fee_payed_amount='".$o_main->db->escape_str(str_replace(",", ".", $_POST['fee_payed_amount']))."',
        interest_payed_amount='".$o_main->db->escape_str(str_replace(",", ".", $_POST['interest_payed_amount']))."',
        printed_amount='".$o_main->db->escape_str($_POST['printed_amount'])."'
		WHERE id = ?";
		$o_main->db->query($s_sql, array($variables->loggID, $v_data['id']));

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_report";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="report_id" value="<?php print $_POST['report_id'];?>">

	<div class="inner">
		<div class="line">
			<div class="lineTitle"><?php echo $formText_SentWithoutFeesAmount_output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="sent_without_fees_amount" value="<?php echo $v_data['sent_without_fees_amount']; ?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_FeesForgivenAmount_output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="fees_forgiven_amount" value="<?php echo $v_data['fees_forgiven_amount']; ?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_FeePayedAmount_output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="fee_payed_amount" value="<?php echo number_format($v_data['fee_payed_amount'], 2, ",", " "); ?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_InterestPayedAmount_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="interest_payed_amount" value="<?php echo number_format($v_data['interest_payed_amount'], 2, ",", " "); ?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_PrintedAmount_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="printed_amount" value="<?php echo $v_data['printed_amount']; ?>" required>
			</div>
			<div class="clear"></div>
		</div>

	</div>
	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
</form>
</div>
<style>

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
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
.popupeditbox label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: none !important;
}
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">


$(function() {
	$(".claimTypeChange").change(function(){
		var value = $(this).val();
		if(value == 1){
			$(".originalClaimInputWrapper").show();
			$(".originalClaimInputWrapper input").prop("required", true);
		} else {
			$(".originalClaimInputWrapper").hide();
			$(".originalClaimInputWrapper input").prop("required", false);
		}
	}).change();
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});
			// data.imagesToProcess = imagesToProcess;
			// data.imagesHandle = imagesHandle;
			// data.images = images;

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: data,
				success: function (data) {
					fw_loading_end();
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");

						});
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
</script>
