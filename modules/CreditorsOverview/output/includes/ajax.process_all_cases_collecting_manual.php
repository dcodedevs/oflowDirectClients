<?php
ini_set('memory_limit','1024M');
ini_set('max_execution_time', 900);
if($_POST['output_form_submit']){
	include(__DIR__."/process_scripts/handle_all_collecting_cases_manual.php");
	return;
} else {
	$set_preview = true;
	include(__DIR__."/process_scripts/handle_all_collecting_cases_manual.php");

	// require_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
	// $s_sql = "SELECT * FROM collecting_company_cases WHERE create_letter = 1 ORDER BY id ASC";
	// $o_query = $o_main->db->query($s_sql);
	// $cases_without_letters = ($o_query ? $o_query->result_array() : array());
	// if(count($cases_without_letters) > 0){
	// 	foreach($cases_without_letters as $cases_without_letter) {
	// 		$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE collecting_company_case_id = ?";
	// 		$o_query = $o_main->db->query($s_sql, array($cases_without_letter['id']));
	// 		$letters = ($o_query ? $o_query->result_array() : array());
	// 		if(count($letters) == 0){
	// 			$result = generate_pdf($cases_without_letter['id']);
	// 	        if(count($result['errors']) > 0) {
	// 	            foreach($result['errors'] as $error) {
	// 	                echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
	// 	            }
	// 	        } else {
	// 	            $successfullyCreatedLetters++;
	// 	        }
	// 		}
	// 	}
	// 	echo $successfullyCreatedLetters;
	// }
	?>
	<div class="popupform popupform-<?php echo $eventId;?>">
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=process_all_cases_collecting_manual"; }?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">

			<input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
			<div class="inner">
				<div class="popupformTitle"><?php
					echo $formText_CasesForManualProcessing_output;
				?></div>
				<?php
				$sql = "SELECT creditor.*, c.name as creditorName FROM creditor
                JOIN customer c ON c.id = creditor.customer_id
				WHERE creditor.content_status < 2 ORDER BY creditor.id";
				$o_query = $o_main->db->query($sql);
				$select_creditors = $o_query ? $o_query->result_array() : array();
				?>
				<select class="selectCreditor" name="selected_creditor_id" autocomplete="off">
					<option value=""><?php echo $formText_All_output;?></option>
					<?php foreach($select_creditors as $creditor) { ?>
						<option value="<?php echo $creditor['id'];?>" <?php if($_POST['selected_creditor_id'] == $creditor['id']) echo 'selected';?>><?php echo $creditor['creditorName'];?></option>
					<?php } ?>
				</select>
				<div class="popupformSubTitle"><?php
					echo $formText_CasesOnWarningLevel_output;
				?></div>
				<div class="caseList">
					<table class="table">
						<tr>
							<th><input type="checkbox" name="all_transactions" class="all_case_selector" autocomplete="off"/></th>
							<th><?php echo $formText_CaseId_output;?></th>
							<th><?php echo $formText_Creditor_output;?></th>
							<th><?php echo $formText_Debitor_output;?></th>
						</tr>
						<?php
						foreach($return_data as $creditorId => $cases){
							foreach($cases['warning_level'] as $case){
								?>
								<tr>
									<td><input type="checkbox" name="collecting_case_ids[]" class="case_id_selector" value="<?php echo $case['id']; ?>" autocomplete="off" /></td>
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
							<th><input type="checkbox" name="all_transactions" class="all_case_selector" autocomplete="off"/></th>
							<th><?php echo $formText_CaseId_output;?></th>
							<th><?php echo $formText_Creditor_output;?></th>
							<th><?php echo $formText_Debitor_output;?></th>
						</tr>
						<?php
						foreach($return_data as $creditorId => $cases){
							foreach($cases['collecting_level'] as $case){
								?>
								<tr>
									<td><input type="checkbox" name="collecting_case_ids[]" class="case_id_selector" value="<?php echo $case['id']; ?>" autocomplete="off" /></td>
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
				<input type="submit" name="sbmbtn" class="process_btn" value="<?php echo $formText_Process_Output; ?>">
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

	$(".all_case_selector").off("click").on("click", function(){
		if($(this).is(":checked")) {
			$(".case_id_selector").prop("checked", true);
		} else {
			$(".case_id_selector").prop("checked", false);
		}
		calculate_total();
	})

	function calculate_total(){
		$(".process_btn").val($(".case_id_selector:checked").length + " "+'<?php echo $formText_Process_output;?>');
	}
	$(".case_id_selector").off("change").on("change", function(){
		calculate_total();
	})
	$(".selectCreditor").off("change").on("change", function(e){
		e.preventDefault();
		var data = {
			selected_creditor_id: $(this).val()
		};
		ajaxCall('process_all_cases_collecting_manual', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata ).each(function(index, obj){
				if(data[obj.name] != undefined) {
					if(Array.isArray(data[obj.name])){
						data[obj.name].push(obj.value);
					} else {
						data[obj.name] = [data[obj.name], obj.value];
					}
				} else {
					data[obj.name] = obj.value;
				}
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
					} else  if(data.html !== "")
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
