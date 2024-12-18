<?php
$username= $variables->loggID;

include(__DIR__."/../../../CollectingCases/output/includes/fnc_generate_pdf.php");
$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
$case = $o_query ? $o_query->row_array() : array();
include(dirname(__FILE__).'/../languagesOutput/no.php');
if($case){
	$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$system_settings = ($o_query ? $o_query->row_array() : array());

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


	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
	$creditor = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
	$invoice = ($o_query ? $o_query->row_array() : array());

	$toBePaid = $invoice['collecting_case_original_claim'];

	$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	$invoice_payments = ($o_query ? $o_query->result_array() : array());

	$total_transaction_payments = $invoice_payments;

	$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	$claim_transactions = ($o_query ? $o_query->result_array() : array());
	// foreach($claim_transactions as $transaction_fee) {
	// 	if(!$transaction_fee['open']) {
	// 		$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
	// 		$o_query = $o_main->db->query($s_sql, array($transaction_fee['link_id'], $transaction_fee['creditor_id']));
	// 		$fee_payments = ($o_query ? $o_query->result_array() : array());
	//
	// 		foreach($fee_payments as $fee_payment) {
	// 			$inOtherTransaction = false;
	// 			foreach($invoice_payments as $transaction_payment) {
	// 				if($transaction_payment['id'] == $fee_payment['id']){
	// 					$inOtherTransaction = true;
	// 				}
	// 			}
	// 			if(!$inOtherTransaction){
	// 				$total_transaction_payments[]=$fee_payment;
	// 			}
	// 		}
	// 	}
	// }

	$payments = 0;
	foreach($total_transaction_payments as $invoice_payment) {
		$payments += $invoice_payment['amount'];
	}
	foreach($claim_transactions as $claim_transaction) {
		$toBePaid += $claim_transaction['amount'];
	}

	$validForRest = false;
	foreach($invoice_payments as $invoice_payment){
		if($invoice_payment['system_type'] == "Payment"){
			if(date("d.m.Y", strtotime($invoice_payment['date'])) == date("d.m.Y", strtotime($case['created']))) {
				if(strtotime($invoice_payment['created']) > strtotime($case['created'])) {
					$validForRest = true;
				}
			} else {
				if(strtotime($invoice_payment['date']) > strtotime($case['created'])) {
					$validForRest = true;
				}
			}
		}
	}
	$reminderRestNoteMinimumAmount = $collecting_system_settings['reminderRestNoteMinimumAmount'];
	if($creditor['use_customized_reminder_rest_note_min_amount']){
		$reminderRestNoteMinimumAmount = $creditor['reminderRestNoteMinimumAmount'];
	}
	$lettersForDownload = array();
	if($validForRest){
		$leftToBePaid = $toBePaid + $payments;
		if($leftToBePaid >= $reminderRestNoteMinimumAmount) {
			$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id='".$o_main->db->escape_str($case['id'])."'
			AND step_id = '".$o_main->db->escape_str($case['collecting_cases_process_step_id'])."' AND rest_note = '1'";
			$o_query = $o_main->db->query($s_sql);
			$rest_letter = $o_query ? $o_query->row_array() : array();
			if(!$rest_letter) {
				//Send note
				do{
					$code = generateRandomString(10);
					$code_check = null;
					$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
					$o_query = $o_main->db->query($s_sql, array($code));
					if($o_query){
						$code_check = $o_query->row_array();
					}
				} while($code_check != null);

				$result = generate_pdf($case['id'], 1);
				if(count($result['errors']) > 0){
					foreach($result['errors'] as $error){
						$v_return['error'] = $formText_LetterFailedToBeCreatedForCase_output." ".$case['id']." ".$error."</br>";
					}
				} else {
					$successfullyCreatedLetters++;
					if($creditor['print_reminders'] == 0) {
						if($result['item']['id'] > 0){
							$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(),  print_batch_code = ? WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($code, $result['item']['id']));
							if($o_query) {
								$lettersForDownload[] = $result['item']['id'];

								if(count($lettersForDownload) > 0) {
								    echo $formText_LettersForManualPrinting_output." <a href='".$extradomaindirroot."/modules/CollectingCaseClaimletter/output/includes/ajax.download.php?code=".$code."&ids=".implode(",",$lettersForDownload)."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>"."<br/>";
								}
							}
						}
					}
				}
			} else {
				$v_return['error'] = $formText_RestNoteAlreadyCreatedForThisStep_output;
			}
		}
	}
} else {
	$v_return['error'] = $formText_MissingCase_output;
}
if($v_return['error']){
    $fw_error_msg[] = $v_return['error'];
	?>
	<div class="popupform popupform-<?php echo $eventId;?>">
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=reset_case"; }?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
			<input type="hidden" name="case_id" value="<?php echo $_POST['case_id'];?>">
			<input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
			<div class="inner">
				<?php
				echo $response['error'];
				?>
			</div>

			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			</div>
		</form>
	</div>
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
						out_popup.addClass("close-reload-creditor");
						out_popup.close();
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
} else {
	$fw_return_data = array("success"=>1);
}
?>
