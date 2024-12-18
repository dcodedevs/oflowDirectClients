<?php
$sql = "SELECT * FROM creditor WHERE choose_progress_of_reminderprocess = 1 AND autosyncing_not_working_date = '0000-00-00 00:00:00' ORDER BY id";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();
require(__DIR__."/../creditor_functions.php");
require(__DIR__."/../fnc_create_case_from_transaction.php");
$nopreview = true;
if($set_preview){
	$nopreview = false;
}

$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$system_settings = ($o_query ? $o_query->row_array() : array());
$s_sql = "SELECT * FROM reminder_minimum_amount ORDER BY currency ASC";
$o_query = $o_main->db->query($s_sql);
$reminder_minimum_amounts = $o_query ? $o_query->result_array() : array();
$default_reminder_minimum_amount_noncurrency = intval($system_settings['default_reminder_minimum_amount_noncurrency']);

//used to output more detailed error messages with transaction syncing
$launchedFromProcessAllScript = true;
foreach($creditors as $creditor) {
	if(intval($creditor['onboarding_incomplete']) == 0){
		$sendToday = false;	
		if($creditor['email_sending_day_choice_reminder'] == 0){
			$sendToday = true;
		} else {
			$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 0";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			$creditor_email_sending_days = ($o_query ? $o_query->result_array() : array());
			foreach($creditor_email_sending_days as $creditor_email_sending_day) {
				if($creditor_email_sending_day['day_number'] == date('N')) {
					if($creditor_email_sending_day['checked']){
						$sendToday = true;
					}
				}
			}
		}
		if($sendToday) {						
			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
			$creditor_person_profile = $o_query ? $o_query->row_array() : array();
			
			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
			$creditor_company_profile = $o_query ? $o_query->row_array() : array();
			if($creditor_company_profile && $creditor_person_profile){
				if(strtotime($creditor['next_automatic_reminder_process_time']) < strtotime(date("Y-m-d 11:00:00")) && $creditor['automatic_reminder_process_running'] == 0 || (strtotime(date("Y-m-d H:i:s"))-strtotime($creditor['next_automatic_reminder_process_time']) >= 86400)) {
					
					$s_sql = "SELECT cc.* FROM collecting_cases cc JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id WHERE cc.creditor_id = ?  ";
					$o_query = $o_main->db->query($s_sql, array($creditor['id']));
					$cases = $o_query ? $o_query->result_array() : array();
					$filters = array();
					$filters['order_field'] = '';
					$filters['order_direction'] = 0;
					$filters['list_filter'] = "canSendReminderNow";
					$customerListNonProcessed = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
					$casesToBeProcessed = array();
					$casesToBeCreated = array();
					$fullCasesToBeProcessed = array();
					$fullCasesToBeCreated = array();
					
					// if($creditor['id'] == 1943){
					// 	var_dump($o_main->db->last_query());
					// }
					foreach($customerListNonProcessed as $v_row) {
						$s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
						$o_query = $o_main->db->query($s_sql, array($v_row['external_customer_id'], $v_row['creditorCreditorId']));
						$debitorCustomer = $o_query ? $o_query->row_array() : array();
						if($debitorCustomer['choose_progress_of_reminderprocess'] == 0 || $debitorCustomer['choose_progress_of_reminderprocess'] == 2) {
							if($v_row['choose_progress_of_reminderprocess'] == 0 || $v_row['choose_progress_of_reminderprocess'] == 2) {
								$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
								$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditorCreditorId']));
								$transaction_payments = ($o_query ? $o_query->result_array() : array());

								$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
								$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditorCreditorId']));
								$transaction_fees = ($o_query ? $o_query->result_array() : array());

								$initialAmount = 0;
								if($v_row['id'] == null) {
									$initialAmount = $v_row['amount'];
								} else {
									$initialAmount = $v_row['totalSumOriginalClaim'];
								}
								$amount = $initialAmount;
								$openFeeAmount = 0;
								foreach($transaction_fees as $transaction_fee) {
									$amount += $transaction_fee['amount'];
									if($transaction_fee['open']) {
										$openFeeAmount += $transaction_fee['amount'];
									}
								}
								foreach($transaction_payments as $transaction_payment) {
									$amount += $transaction_payment['amount'];
								}
								$actionType = 0;
								if($v_row['nextStepActionType'] == 2) {
									if($v_row['invoiceEmail'] != "") {
										$actionType = 1;
									}
								}
								$missingAddress = false;
								if($actionType == 0){
									if($debitorCustomer['paStreet'] == "" && $debitorCustomer['paPostalNumber'] == "" && $debitorCustomer['paCity'] == "") {
										$missingAddress = true;
									}
								}
								$currencyNameToCompare = "";
								// if($variables->loggID == "david@dcode.no"){
								// 	var_dump($v_row['internalTransactionId']);
								// }

								if($v_row['currency'] != "LOCAL" && $v_row['currency'] != "") {
									$currencyNameToCompare = $v_row['currency']." ";
								} else {
									$currencyNameToCompare = $creditor['default_currency'];
								}

								$minimum_amount_for_processing = intval($default_reminder_minimum_amount_noncurrency);
								foreach($reminder_minimum_amounts as $reminder_minimum_amount) {
									if(trim($currencyNameToCompare) != ""){
										if(mb_strtolower($reminder_minimum_amount['currency']) == mb_strtolower(trim($currencyNameToCompare))) {
											$minimum_amount_for_processing = $reminder_minimum_amount['amount'];
										}
									} else {
										if(mb_strtolower($reminder_minimum_amount['currency']) == "nok") {
											$minimum_amount_for_processing = $reminder_minimum_amount['amount'];
										}
									}
								}
								if(!$missingAddress) {
									if($v_row['id'] == null){
										if($amount > $minimum_amount_for_processing) {
											if($v_row['invoice_nr'] > intval($creditor['reminder_only_from_invoice_nr'])) {
												if(in_array($v_row['internalTransactionId'], $_POST['transaction_ids'])){
													$casesToBeCreated[] = $v_row['internalTransactionId'];
												}
												$fullCasesToBeCreated[] = $v_row;
											} else {
												$notSendInfo = $formText_InvoicesBeforeNumber_output." ".intval($creditor['reminder_only_from_invoice_nr'])." ".$formText_ShouldNotSendReminders_output;
											}
										}
									} else {
										if(strtotime($v_row['nextStepDate']) <= time() && ($amount) > $minimum_amount_for_processing){
											if($v_row['invoice_nr'] > intval($creditor['reminder_only_from_invoice_nr'])) {
												if(in_array($v_row['id'], $_POST['case_ids'])){
													$casesToBeProcessed[] = $v_row['id'];
												}
												$fullCasesToBeProcessed[] = $v_row;
											} else {
												if(in_array($v_row['internalTransactionId'], $_POST['case_ids'])){
													$notSendInfo = $formText_InvoicesBeforeNumber_output." ".intval($creditor['reminder_only_from_invoice_nr'])." ".$formText_ShouldNotSendReminders_output."</br>";
												}
											}
										}
									}
								} else {
									if(in_array($v_row['internalTransactionId'], $_POST['case_ids']) || in_array($v_row['internalTransactionId'], $_POST['transaction_ids'])){
										$notSendInfo = $formText_MissingAddress_output."</br>";
									}
								}
							}
						} else {
							if(in_array($v_row['internalTransactionId'], $_POST['case_ids']) || in_array($v_row['internalTransactionId'], $_POST['transaction_ids'])){
								$notSendInfo = $formText_Customer_output."</br>";
							}
						}
					}
					if(count($fullCasesToBeProcessed) > 0 || count($fullCasesToBeCreated)) {
						if(strtotime($creditor['lastImportedDate'])+30*60 < time()){
							$creditorsToSync[] = $creditor['id'];
							$creditorsToSyncFull[] = $creditor;
						}
					}
					if($nopreview) {
						echo $creditor['companyname']." ".$notSendInfo;
						if(!$creditor['automatic_reminder_process_running']){
							$sql = "UPDATE creditor SET automatic_reminder_process_running = 1 WHERE id = ?";
							$o_query = $o_main->db->query($sql, array($creditor['id']));
						}
						$fromMultiCreditorProcessing=true;
						if(count($casesToBeCreated) > 0) {
							$newCaseCount = 0;
							foreach($casesToBeCreated as $singleToProcess) {
								$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($singleToProcess));
								$transaction = ($o_query ? $o_query->row_array() : array());
								$languageID = 'no';
								if($transaction) {
									$caseCreated = create_case_from_transaction($transaction['id'], $creditor['id'], $languageID, false);
									if($caseCreated > 0) {
										$newCaseCount++;
										$casesReported[] = $caseCreated;
									}
								}
							}
							if(count($casesReported) > 0){
								$manualProcessing = 1;
								$creditorId = $creditor['id'];
								$collecting_case_id = $casesReported;
								include(__DIR__."/../../../output/includes/process_scripts/handle_cases.php");
							}
						}
						if(count($casesToBeProcessed) > 0){
							$manualProcessing = 1;
							$creditorId = $creditor['id'];
							$collecting_case_id = $casesToBeProcessed;
							include(__DIR__."/../../../output/includes/process_scripts/handle_cases.php");
						}
						$sql = "UPDATE creditor SET automatic_reminder_process_running = 0 WHERE id = ?";
						$o_query = $o_main->db->query($sql, array($creditor['id']));
					} else {
						$return_data[$creditor['id']]['casesToBeProcessed'] = $fullCasesToBeProcessed;
						$return_data[$creditor['id']]['casesToBeCreated'] = $fullCasesToBeCreated;
					}
				} else {
					echo $creditor['companyname']." ".$formText_NotReachedTime_output." ".date("d.m.Y H:i:s", strtotime($creditor['next_automatic_reminder_process_time']))."<br/>";
				}
			} else {
				echo $creditor['companyname']." ".$formText_MissingProfiles_output;
				if($creditor['skip_reminder_go_directly_to_collecting'] > 0) {
					echo " " . $formText_SkipReminderGoDirectlyToCollecting_output;
				}
				echo "<br/>";
				
			}
		}
	}
}

?>