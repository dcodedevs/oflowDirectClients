<?php
$collecting_case_id = $_POST['collecting_case_id'];

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['objection_closed_handling_description'] != "") {
			if(isset($_POST['cid']) && $_POST['cid'] > 0)
			{
				$s_sql = "SELECT * FROM collecting_cases_objection WHERE id = ?";
			    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
			    if($o_query && $o_query->num_rows() == 1) {
					$s_sql = "UPDATE collecting_cases_objection SET
					updated = now(),
					updatedBy= ?,
					objection_closed_date = NOW(),
					objection_closed_by = ?,
					objection_closed_handling_description = ?
					WHERE id = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $variables->loggID, $_POST['objection_closed_handling_description'], $_POST['cid']));
				}
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
				return;
			} else {
				$fw_error_msg[] = $formText_MissingObjection_output;
			}
		} else {
			$fw_error_msg[] = $formText_FillInMessage_output;
		}
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_objection WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
$type_messages = array("", $formText_WantsInvoiceCopy_output,$formText_WantsDefermentOfPayment_output,$formText_WantsInstallmentPayment_output,$formText_HasAnObjectionToTheAmount_output,$formText_HasAnObjectionToTheProductService_output);

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=close_objection";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
		<input type="hidden" name="collecting_case_id" value="<?php print $_POST['collecting_case_id'];?>">


		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ClosedHandlingDescription_Output; ?></div>
				<div class="lineInput"><textarea class="popupforminput botspace" name="objection_closed_handling_description" required><?php echo $v_data['objection_closed_handling_description'];?></textarea></div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_CloseObjection_Output; ?>"></div>
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
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(function() {
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
						$("#popup-validate-message").html("");
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show()
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
