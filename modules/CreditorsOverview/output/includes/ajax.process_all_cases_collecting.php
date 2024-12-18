<?php
if($_POST['output_form_submit']){
	include(__DIR__."/process_scripts/handle_all_collecting_cases.php");
	return;
} else {
	$set_preview = true;
	include(__DIR__."/process_scripts/handle_all_collecting_cases.php");
	?>
	<div class="popupform popupform-<?php echo $eventId;?>">
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=process_all_cases_collecting"; }?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
			<input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
			<div class="inner">
				<div class="popupformTitle"><?php
					echo $formText_CasesToBeProcessed_output;
				?></div>
				<div class="popupformSubTitle"><?php
					echo $formText_CasesOnWarningLevel_output;
				?></div>
				<div class="caseList">
					<table class="table">
						<tr>
							<th><?php echo $formText_CaseId_output;?></th>
							<th><?php echo $formText_Creditor_output;?></th>
							<th><?php echo $formText_Debitor_output;?></th>
						</tr>
						<?php
						foreach($return_data as $creditorId => $cases){
							foreach($cases['warning_level'] as $case){
								?>
								<tr>
									<td><?php echo $case['id'];?></td>
									<td><?php echo $case['creditorName'];?></td>
									<td><?php echo $case['debitorName'];?></td>
								</tr>
								<?php
							}
						}
						?>
					</table>
				</div>
				<div class="popupformSubTitle"><?php
					echo $formText_CasesOnCollectingLevel_output;
				?></div>
				<div class="caseList">
					<table class="table">
						<tr>
							<th><?php echo $formText_CaseId_output;?></th>
							<th><?php echo $formText_Creditor_output;?></th>
							<th><?php echo $formText_Debitor_output;?></th>
						</tr>
						<?php
						foreach($return_data as $creditorId => $cases){
							foreach($cases['collecting_level'] as $case){
								?>
								<tr>
									<td><?php echo $case['id'];?></td>
									<td><?php echo $case['creditorName'];?></td>
									<td><?php echo $case['debitorName'];?></td>
								</tr>
								<?php
							}
						}
						?>
					</table>
				</div>
			</div>

			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
				<input type="submit" name="sbmbtn" value="<?php echo $formText_Process_Output; ?>">
			</div>
		</form>
	</div>
	<style>
	.popupformSubTitle {
		font-size: 14px;
		margin-bottom: 5px;
	}
	</style>
	<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">

	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});
			$("#popup-validate-message").hide();

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
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else  if(data.redirect_url !== undefined)
					{
						$('#popupeditboxcontent').html('');
		   				$('#popupeditboxcontent').html(data.html);
		   				out_popup = $('#popupeditbox').bPopup(out_popup_options);
		   				$("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
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
	</script>
	<?php
}
?>
