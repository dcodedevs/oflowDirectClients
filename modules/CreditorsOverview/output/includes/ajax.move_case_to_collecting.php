<?php
$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$transaction_id= $_POST['transaction_id'];
$process_id= $_POST['process_id'];
$username= $variables->loggID;
$languageID = $variables->languageID;
$case_choice = intval($_POST['case_choice']);
$company_case_id = intval($_POST['company_case_id']);

$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($process_id));
$collectingProcess = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction_id));
$transaction = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction['collectingcase_id']));
$case = ($o_query ? $o_query->row_array() : array());

include(__DIR__."/../../output/languagesOutput/default.php");
if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")) {
	include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
}
function proc_tverrsum($tall){
	return array_sum(str_split($tall));
}
function proc_mod10( $kid_u ){
    $siffer = str_split(strrev($kid_u));
    $sum = 0;

    for($i=0; $i<count($siffer); ++$i) $sum += proc_tverrsum(( $i & 1 ) ? $siffer[$i] * 1 : $siffer[$i] * 2);


	$controlnumber = ($sum==0) ? 0 : 10 - substr($sum, -1);
	if ($controlnumber == 10) $controlnumber = 0;
    return $controlnumber;
}

if(!function_exists("generate_case_kidnumber")){
    function generate_case_kidnumber($creditorId, $caseId){
		$kidnumber = "";

		$emptynumber = 7 - strlen($creditorId);
		for($i = 0;$i<$emptynumber;$i++)
			$kidnumber .="0";
		$kidnumber .= $creditorId;

		$emptynumber = 10 - strlen($caseId);
		for($i = 0;$i<$emptynumber;$i++)
			$kidnumber .= "0";
		$kidnumber .= $caseId;

		$controlnumber = proc_mod10($kidnumber);

		$kidnumber .= $controlnumber;
		return $kidnumber;
    }
}

require(__DIR__."/../../output/includes/fnc_move_transaction_to_collecting.php");
$v_return = move_transaction_to_collecting($transaction_id, $process_id, $username);
if($v_return['status']){
	$fw_return_data = 1; 
} else {
	$fw_error_msg[] = $v_return['error'];
}
// if($collectingProcess) {
//     if($transaction) {
// 		if(intval($case['status']) == 1 || intval($case['status']) == 0) {
//
// 			$s_sql = "SELECT customer.* FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($transaction['external_customer_id'], $transaction['creditor_id']));
// 			$customer = ($o_query ? $o_query->row_array() : array());
//
// 			$createCase = false;
// 			$s_sql = "SELECT * FROM collecting_company_cases WHERE creditor_id = ? AND debitor_id = ? AND (collecting_cases_process_step_id is null OR collecting_cases_process_step_id = 0)";
// 			$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id'], $customer['id']));
// 			$notStartedCollectingCase = ($o_query ? $o_query->row_array() : array());
// 			if($notStartedCollectingCase){
// 				$col_company_case_id = $notStartedCollectingCase['id'];
// 			} else {
// 				$createCase = true;
// 			}
// 			// if($col_company_case_id > 0 && $case_choice == 0) {
// 				// $s_sql = "SELECT collecting_company_cases.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName FROM collecting_company_cases
// 				// LEFT OUTER JOIN customer c ON c.id = collecting_company_cases.debitor_id
// 				// WHERE collecting_company_cases.creditor_id = ? AND collecting_company_cases.debitor_id = ?
// 				// AND (collecting_company_cases.collecting_cases_process_step_id is null OR collecting_company_cases.collecting_cases_process_step_id = 0) ORDER BY collecting_company_cases.id ASC";
// 				// $o_query = $o_main->db->query($s_sql, array($transaction['creditor_id'], $customer['id']));
// 				// $notStartedCollectingCases = ($o_query ? $o_query->result_array() : array());
// 				// $v_return['status'] = 1;
// 				// $v_return['existing_cases'] = $notStartedCollectingCases;
// 				// return;
// 			// }
// 			// if($col_company_case_id > 0){
// 				// if($case_choice == 2){
// 				// 	$createCase = true;
// 				// } else {
// 				// 	$col_company_case_id = $company_case_id;
// 				// }
// 			// }
// 			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
// 			$creditor = ($o_query ? $o_query->row_array() : array());
// 			$reminder_bookaccount = 8070;
// 			$interest_bookaccount = 8050;
// 			if($creditor['reminder_bookaccount'] != ""){
// 				$reminder_bookaccount = $creditor['reminder_bookaccount'];
// 			}
// 			if($creditor['interest_bookaccount'] != ""){
// 				$interest_bookaccount = $creditor['interest_bookaccount'];
// 			}
// 			$noFeeError3 = true;
// 			if($case) {
// 				$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ? AND creditor_id = ?";
// 				$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
// 				$invoice = $o_query ? $o_query->row_array() : array();
// 				if($invoice){
// 					$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND invoice_nr = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%'";
// 					$o_query = $o_main->db->query($s_sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
// 					$fee_transactions = $o_query ? $o_query->result_array() : array();
// 					if(count($fee_transactions) > 0) {
// 						$noFeeError3 = false;
// 					}
// 					$noFeeError3count = 0;
// 					foreach($fee_transactions as $fee_transaction){
// 						$commentArray = explode("_",$fee_transaction['comment']);
// 						if($commentArray[2] == "interest"){
// 						   	$transactionType = "interest";
// 						} else if($commentArray[2] == "reminderFee"){
// 						  	$transactionType = "reminderFee";
// 						} else if($commentArray[0] == "Rente"){
// 							$transactionType = "interest";
// 						} else {
// 							$transactionType = "reminderFee";
// 						}
// 						$hook_params = array(
// 							'transaction_id' => $fee_transaction['id'],
// 							'amount'=>$fee_transaction['amount']*(-1),
// 							'dueDate'=>$dueDate,
// 							'text'=>$commentArray[0],
// 							'type'=>$transactionType,
// 							'accountNo'=>$commentArray[1],
// 							'close'=> 1
// 						);
//
// 						$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
// 						if (file_exists($hook_file)) {
// 							include $hook_file;
// 							if (is_callable($run_hook)) {
// 								$hook_result = $run_hook($hook_params);
// 								if($hook_result['result']){
// 									$noFeeError3count++;
// 								} else {
// 									// var_dump("deleteError".$hook_result['error']);
// 								}
// 							}
// 						}
// 					}
//
// 					if($noFeeError3count == count($fee_transactions)){
// 						$noFeeError3 = true;
// 					}
// 				}
// 			}
// 			if($noFeeError3) {
// 				$status = 3;
// 				if($collectingProcess['with_warning']){
// 					$status = 7;
// 				}
// 				if($createCase) {
// 					$s_sql = "INSERT INTO collecting_company_cases SET
// 					created = now(),
// 					createdBy='".$o_main->db->escape_str($username)."',
// 					creditor_id='".$o_main->db->escape_str($transaction['creditor_id'])."',
// 					debitor_id='".$o_main->db->escape_str($customer['id'])."',
// 					collecting_process_id = '".$o_main->db->escape_str($collectingProcess['id'])."',
// 					status = '".$o_main->db->escape_str($status)."'";
// 					$o_query = $o_main->db->query($s_sql);
// 					if($o_query) {
// 						$col_company_case_id =  $o_main->db->insert_id();
// 						$kidNumber = generate_case_kidnumber($creditor['id'], $col_company_case_id);
// 						$s_sql = "UPDATE collecting_company_cases SET
// 						kid_number = '".$o_main->db->escape_str($kidNumber)."'
// 						WHERE id = '".$o_main->db->escape_str($col_company_case_id)."'";
// 						$o_query = $o_main->db->query($s_sql);
// 					}
// 				}
// 				if($col_company_case_id > 0){
// 					if($case){
// 						$s_sql = "UPDATE collecting_cases SET
// 						updated = now(),
// 						updatedBy='".$o_main->db->escape_str($username)."',
// 						status = 2,
// 						sub_status = 5,
// 						stopped_date = NOW()
// 						WHERE id = '".$o_main->db->escape_str($case['id'])."'";
// 						$o_query = $o_main->db->query($s_sql);
// 					}
//
// 			        $v_return['status'] = 1;
// 					$restAmount = $transaction['amount'];
//
// 					$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
// 		            $o_query = $o_main->db->query($s_sql, array($transaction['link_id'], $transaction['creditor_id']));
// 		            $transaction_payments = ($o_query ? $o_query->result_array() : array());
//
// 					foreach($transaction_payments as $transaction_payment){
// 						$restAmount += $transaction_payment['amount'];
// 					}
// 					$invoiceDate = "0000-00-00";
// 					if($transaction){
// 						$invoiceDate = $transaction['date'];
// 					}
// 					$dueDate = $transaction['due_date'];
// 					if($case){
// 						$dueDate = $case['due_date'];
// 					}
//
// 					$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
// 					id=NULL,
// 					moduleID = ?,
// 					created = now(),
// 					createdBy= ?,
// 					collecting_company_case_id = ?,
// 					name= ?,
// 					date = ?,
// 		            original_due_date=?,
// 		            claim_type ='1',
// 					amount = ?,
// 					original_amount = ?,
// 					invoice_nr = ?";
// 					$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $col_company_case_id, $formText_InvoiceNumber_output." ".$transaction['invoice_nr'], $invoiceDate, $dueDate, $restAmount, $transaction['amount'], $transaction['invoice_nr']));
//
//
// 					$s_sql = "UPDATE creditor_transactions SET
// 					updated = now(),
// 					updatedBy= ?,
// 					collecting_company_case_id= ?
// 					WHERE id = ?";
// 					$o_main->db->query($s_sql, array($username, $col_company_case_id, $transaction['id']));
//
// 					// $originalDueDate = "";
// 					// if($_POST['original_due_date'] != "") {
// 					// 	$originalDueDate = date("Y-m-d", strtotime($_POST['original_due_date']));
// 					// }
//
// 					// $s_sql = "SELECT * FROM collecting_company_cases WHERE creditor_id = ? AND debitor_id = ? AND (collecting_cases_process_step_id is null OR collecting_cases_process_step_id = 0)";
// 					// $o_query = $o_main->db->query($s_sql, array($case['creditor_id'], $case['debitor_id']));
// 					// $notStartedCollectingCase = ($o_query ? $o_query->row_array() : array());
// 					//
// 				    // if($o_query && $o_query->num_rows() == 1) {
// 				    // 	$case = ($o_query ? $o_query->row_array() : array());
// 					// 	$s_sql = "UPDATE collecting_company_cases_claim_lines SET
// 					// 	updated = now(),
// 					// 	updatedBy= ?,
// 					// 	name= ?,
// 			        //     original_due_date='".$o_main->db->escape_str($originalDueDate)."',
// 			        //     claim_type=1,
// 					// 	amount= ?
// 					// 	WHERE id = ?";
// 					// 	$o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['amount'], $case['id']));
// 					// } else {
// 					// 	$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
// 					// 	id=NULL,
// 					// 	moduleID = ?,
// 					// 	created = now(),
// 					// 	createdBy= ?,
// 					// 	collecting_company_case_id = ?,
// 					// 	name= ?,
// 			        //     original_due_date='".$o_main->db->escape_str($originalDueDate)."',
// 			        //     claim_type='1',
// 					// 	amount= ?";
// 					// 	$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $insert_id, $_POST['name'], $_POST['amount']));
// 					// 	$_POST['cid'] = $o_main->db->insert_id();
// 					// }
// 				} else {
// 					$fw_error_msg[] = 'Error creating case';
// 				}
// 			} else {
// 				$fw_error_msg[] = 'Error closing fees';
// 			}
// 		} else {
// 			$fw_error_msg[]  = 'Case not active';
// 		}
//     } else {
//         $fw_error_msg[]  = 'Case not found';
//     }
// } else {
// 	$fw_error_msg[]  = 'Process not found';
// }

if(count($existing_cases) > 0){
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=move_case_to_collecting";?>" method="post">
        	<input type="hidden" name="fwajax" value="1">
        	<input type="hidden" name="fw_nocss" value="1">
        	<input type="hidden" name="output_form_submit" value="1">
        	<input type="hidden" name="transaction_id" value="<?php print $_POST['transaction_id'];?>">
        	<input type="hidden" name="process_id" value="<?php print $_POST['process_id'];?>">

        	<div class="inner">
				<div class="popupformTitle"><?php echo $formText_CaseAlreadyExists_output;?></div>
				<div class="line">
					<?php
					foreach($existing_cases as $existing_case){
				 		echo "<div><input type='radio' name='company_case_id' id='caseId".$existing_case['id']."' value='".$existing_case['id']."' ><label for='caseId".$existing_case['id']."'>".$formText_CaseId_output." ".$existing_case['id']." - ".$existing_case['debitorName']."</label></div>";
				 	}
				 ?>
				</div>
            </div>
        	<div class="popupformbtn">
	    		<button type="button" name="cancel"  class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
	    		<button type="submit" name="case_choice" value="1" class="output-btn b-large"><?php echo $formText_AddToExistingCase_Output;?></button>
	    		<button type="submit" name="case_choice" value="2" class="output-btn b-large"><?php echo $formText_CreateNewCase_Output;?></button>
			</div>
        </form>
    </div>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
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
					} else  {
						out_popup.addClass("close-reload-creditor").close();
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
