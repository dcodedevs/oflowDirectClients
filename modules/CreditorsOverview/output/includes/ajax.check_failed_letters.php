<?php

if(!function_exists("generateRandomString")) {
	function generateRandomString($length = 8) {
		$characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
include(__DIR__."/../../../CollectingCases/output/includes/fnc_generate_pdf.php");

$s_sql = "SELECT collecting_cases.*, CONCAT_WS(' ', c.name, c.middlename,c.lastname) as debitorName, cred.companyname as creditorName FROM collecting_cases
LEFT OUTER JOIN customer c ON c.id = collecting_cases.debitor_id
LEFT OUTER JOIN creditor cred ON cred.id = collecting_cases.creditor_id
WHERE create_letter = 1";
$o_query = $o_main->db->query($s_sql);
$collecting_cases_with_failed_letter = ($o_query ? $o_query->result_array() : array());
if($_POST['output_form_submit']) {
	$lettersForDownload = array();
	do{
		$code = generateRandomString(10);
		$code_check = null;
		$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
		$o_query = $o_main->db->query($s_sql, array($code));
		if($o_query){
			$code_check = $o_query->row_array();
		}
	} while($code_check != null);

	foreach($collecting_cases_with_failed_letter as $collecting_case_with_failed_letter) {
		if(in_array($collecting_case_with_failed_letter['id'], $_POST['case_ids'])){
			$caseToGenerate = $collecting_case_with_failed_letter['id'];
			$s_sql = "SELECT * FROM collecting_cases WHERE id = ? AND create_letter = 1";
			$o_query = $o_main->db->query($s_sql, array($caseToGenerate));
			$caseData = ($o_query ? $o_query->row_array() : array());

			$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
			$step = ($o_query ? $o_query->row_array() : array());

			if($caseData['stopped_date'] == '0000-00-00 00:00:00' || $caseData['stopped_date'] == "") {
				$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
				$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
				$creditor = ($o_query ? $o_query->row_array() : array());

				$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ? AND creditor_id = ?";
				$o_query = $o_main->db->query($s_sql, array($caseData['reminder_profile_id'], $caseData['creditor_id']));
				$profile = $o_query ? $o_query->row_array() : array();
				if($profile){
	                $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
	                $o_query = $o_main->db->query($s_sql, array($profile['reminder_process_id']));
	                $process = ($o_query ? $o_query->row_array() : array());
					if($process){
						$s_sql = "UPDATE collecting_cases SET updated = NOW(), due_date = ? WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array(date("Y-m-d", strtotime("+".$step['add_number_of_days_to_due_date']." days", time())), $caseData['id']));
						if($o_query) {
							$result = generate_pdf($caseToGenerate);
							if(count($result['errors']) > 0){
								foreach($result['errors'] as $error){
									echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
								}
							} else {
								$successfullyCreatedLetters++;
								$without_fee = 1;

								$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
								$o_query = $o_main->db->query($s_sql, array($profile['id']));
								$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();

								$profile_values = array();
								foreach($unprocessed_profile_values as $unprocessed_profile_value) {
									$profile_values[$profile['reminder_process_id']][$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
								}

								$profile_value = $profile_values[$process['id']][$step['id']];

								if(!$case['doNotAddLateFee']) {
									$doNotAddFee = $step['doNotAddFee'];
									if($profile_value['doNotAddFee'] > 0){
										$doNotAddFee = $profile_value['doNotAddFee'] - 1;
									}
									if(!$doNotAddFee){
										if($step['reminder_transaction_text'] != "") {
											$without_fee = 0;
										}
									}
								}
								$doNotAddInterest = $step['doNotAddInterest'];
								if($profile_value['doNotAddInterest'] > 0){
									$doNotAddInterest = $profile_value['doNotAddInterest'] - 1;
								}
								if(!$doNotAddInterest) {
									$without_fee = 0;
								}

								$s_sql = "UPDATE collecting_cases_claim_letter SET fees_status = ? WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($without_fee, $result['item']['id']));

								if($creditor['print_reminders'] == 0) {
									if($result['item']['id'] > 0){
										$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(),  print_batch_code = ? WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($code, $result['item']['id']));
										if($o_query) {
											$lettersForDownload[] = $result['item']['id'];
										}
									}
								}
							}
						}
					}
				} else {
					echo 'Case missing profile '.$caseData['id'].'<br/>';
				}
			} else {
				$s_sql = "UPDATE collecting_cases SET updated = NOW(), create_letter = 0 WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($caseData['id']));
			}
		}
	}
	if(count($lettersForDownload) > 0){
		echo $formText_LettersForManualPrinting_output." <a href='".$extradomaindirroot."/modules/CollectingCaseClaimletter/output/includes/ajax.download.php?code=".$code."&ids=".implode(",",$lettersForDownload)."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>"."<br/>";
	}
	echo $successfullyCreatedLetters ." letters created";
} else {
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=check_failed_letters";?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">


			<div class="inner">
				<div class="popupformTitle"><?php
					echo $formText_CasesWithLettersFailedToBeCreated_output;
				?></div>
				<table class="table">
					<tr>
						<th><input type="checkbox" name="select_all" autocomplete="off" class="select_all_cases"/></th>
						<th><?php echo $formText_CaseId_output;?></th>
						<th><?php echo $formText_Creditor_output;?></th>
						<th><?php echo $formText_Debitor_output;?></th>
					</tr>
					<?php
					foreach($collecting_cases_with_failed_letter as $collecting_case_with_failed_letter) {
						?>
						<tr>
							<td><input type="checkbox" name="case_ids[]" class="transaction_checkbox" autocomplete="off" value="<?php echo $collecting_case_with_failed_letter['id'];?>"/></td>
							<td><?php echo $collecting_case_with_failed_letter['id'];?></td>
							<td><?php echo $collecting_case_with_failed_letter['creditorName'];?></td>
							<td><?php echo $collecting_case_with_failed_letter['debitorName'];?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<div class="popupformbtn">
					<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
					<input type="submit" name="sbmbtn" class="process_btn" value="<?php echo $formText_Process_Output; ?>">
				</div>
			</div>
		</form>
	</div>
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">

	$(function() {
		$(".select_all_cases").off("click").on("click", function(){
			if($(this).is(":checked")) {
				$(".transaction_checkbox").prop("checked", true);
			} else {
				$(".transaction_checkbox").prop("checked", false);
			}
			calculate_total();
		})
		function calculate_total(){
			$(".process_btn").val($(".transaction_checkbox:checked").length + " "+'<?php echo $formText_Process_output;?>');
		}
		$(".transaction_checkbox").off("change").on("change", function(){
			calculate_total();
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
	});
	</script>
	<?php
}
?>
