<?php
include(__DIR__."/../../../../CollectingCases/output/includes/fnc_calculate_interest.php");
include(__DIR__."/../../../../CollectingCases/output/includes/fnc_generate_pdf.php");
include_once(__DIR__."/../fnc_process_open_cases_for_tabs.php");
//$creditorId
$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditorId));
$creditor = ($o_query ? $o_query->row_array() : array());
$reminder_bookaccount = 8070;
$interest_bookaccount = 8050;
if($creditor['reminder_bookaccount'] != ""){
    $reminder_bookaccount = $creditor['reminder_bookaccount'];
}
if($creditor['interest_bookaccount'] != ""){
    $interest_bookaccount = $creditor['interest_bookaccount'];
}
$type_no = "";
$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/get_type_no.php';
if (file_exists($hook_file)) {
	include $hook_file;
	if (is_callable($run_hook)) {
		$hook_params = array('creditor_id'=>$creditor['id']);
		$hook_result = $run_hook($hook_params);
		if($hook_result['result']) {
			$type_no = $hook_result['result'];
		}
	}
}

$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
$creditor_person_profile = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_person_profile['id']));
$creditor_person_values = $o_query ? $o_query->result_array() : array();


$creditor_person_profile_values = array();
foreach($creditor_person_values as $creditor_person_value) {
	$creditor_person_profile_values[$creditor_person_profile['reminder_process_id']][$creditor_person_value['collecting_cases_process_step_id']] = $creditor_person_value;
}
$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
$creditor_company_profile = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_company_profile['id']));
$creditor_company_values = $o_query ? $o_query->result_array() : array();

$creditor_company_profile_values = array();
foreach($creditor_company_values as $creditor_company_value) {
	$creditor_company_profile_values[$creditor_company_profile['reminder_process_id']][$creditor_company_value['collecting_cases_process_step_id']] = $creditor_company_value;
}

$creditor_move_to_collecting = $creditor['choose_move_to_collecting_process'];
$creditor_progress_of_reminder_process = $creditor['choose_progress_of_reminderprocess'];

$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$system_settings = ($o_query ? $o_query->row_array() : array());

// $objection_after_days = intval($system_settings['default_days_after_closed_objection_to_process']);
// if($creditor['days_after_closed_objection_to_process'] != NULL){
//     $objection_after_days = $creditor['days_after_closed_objection_to_process'];
// }
if(intval($creditor['choose_progress_of_reminderprocess']) == 0){
    $creditor['choose_how_to_create_collectingcase'] = 0;
}
$onhold_sql = " AND (cc.onhold_by_creditor is null OR cc.onhold_by_creditor = 0)";
if($skip_to_step > 0){
    $onhold_sql = "";
}
$collectingcase_status_sql = " AND ct.open = 1";
if($system_settings['locked']) {
	echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$system_settings['locked_message']."<br/>";
} else if($type_no == "") {
	echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_FailedToRetreiveCustomerInvoiceTypeNo_output." ".$formText_PleaseContactSupport_output."<br/>";
} else {
	$cases = array();
	if(isset($collecting_case_id)){
	    if(!is_array($collecting_case_id)){
	        $collecting_case_id = array($collecting_case_id);
	    }
	    $s_sql = "SELECT cc.* FROM collecting_cases cc JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id WHERE cc.creditor_id = ? ".$collectingcase_status_sql.$onhold_sql." AND cc.id IN (".implode(",", $collecting_case_id).")";
	    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
	    $cases = $o_query ? $o_query->result_array() : array();
	}
	// var_dump($cases);
	if($skip_to_step > 0){
	    $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($skip_to_step));
	    $skip_to_step_item = ($o_query ? $o_query->row_array() : array());
	}
	$casesToGenerate = array();

	$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW(), createdBy = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id'], $variables->loggID));
	if($o_query){
		$creditor_syncing_id = $o_main->db->insert_id();
	}
	$total_hook_params = array();
	$casesToContinueProcessing = array();
	$casesToContinueProcessingIds = array();
	$cases_to_move = array();
	foreach($cases as $case)
	{
	    if(intval($case['create_letter']) == 0) {
	        $s_sql = "SELECT * FROM customer WHERE id = ?";
	        $o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
	        $debitorCustomer = $o_query ? $o_query->row_array() : array();

			$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
			$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];

			$profile = array();

			$case_progress_of_reminder_process = $case['choose_progress_of_reminderprocess'];
			if($case_progress_of_reminder_process == 0){
				if($customer_progress_of_reminder_process == 0){
					$case_progress_of_reminder_process = $creditor_progress_of_reminder_process;
				} else {
					$case_progress_of_reminder_process = $customer_progress_of_reminder_process - 1;
				}
			} else {
				$case_progress_of_reminder_process--;
			}

			if($case['reminder_profile_id'] > 0) {
				$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ? AND creditor_id = ?";
				$o_query = $o_main->db->query($s_sql, array($case['reminder_profile_id'], $case['creditor_id']));
				$profile = $o_query ? $o_query->row_array() : array();
			}
			// if(!$profile) {
			// 	if($debitorCustomer['creditor_reminder_profile_id'] > 0){
			// 		$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
			// 		$o_query = $o_main->db->query($s_sql, array($debitorCustomer['creditor_reminder_profile_id']));
			// 		$profile = $o_query ? $o_query->row_array() : array();
			// 	}
			// 	if(!$profile){
			// 		if($debitorCustomer['customer_type_collect'] == 0){
			// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
			// 			$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
			// 			$profile = $o_query ? $o_query->row_array() : array();
			// 		} else {
			// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
			// 			$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
			// 			$profile = $o_query ? $o_query->row_array() : array();
			// 		}
			// 	}
			// }
			if($case_progress_of_reminder_process != 2) {
				if($profile) {
					$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
					$o_query = $o_main->db->query($s_sql, array($profile['id']));
					$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();

					$profile_values = array();
					foreach($unprocessed_profile_values as $unprocessed_profile_value) {
						$s_sql = "SELECT * FROM creditor_reminder_custom_profile_value_fees WHERE creditor_reminder_custom_profile_value_id = ? ORDER BY mainclaim_from_amount ASC";
						$o_query = $o_main->db->query($s_sql, array($unprocessed_profile_value['id']));
						$fees = ($o_query ? $o_query->result_array() : array());
						$unprocessed_profile_value['fees'] = $fees;

						$profile_values[$profile['reminder_process_id']][$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
					}

			        // $s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE collecting_case_id = ? AND (status = 0 OR status is null) ORDER BY sortnr ASC";
			        // $o_query = $o_main->db->query($s_sql, array($case['id']));
			        // $active_payment_plan = ($o_query ? $o_query->row_array() : array());
			        // if(!$active_payment_plan) {
		            if($case['onhold_by_creditor'] != 1 || $skip_to_step > 0){
		                $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
		                $o_query = $o_main->db->query($s_sql, array($profile['reminder_process_id']));
		                $process = ($o_query ? $o_query->row_array() : array());

						$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
						$invoice = ($o_query ? $o_query->row_array() : array());
						if($invoice['open'] == 1){
							$currencyName = "";
							$invoiceDifferentCurrency = false;
							if($invoice['currency'] != ""){
								if($invoice['currency'] == 'LOCAL') {
									$currencyName = trim($creditor['default_currency']);
								} else {
									$currencyName = trim($invoice['currency']);
									$invoiceDifferentCurrency = true;
								}
								if($currencyName != "") {
									if($process) {
										$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
										$o_query = $o_main->db->query($s_sql, array($process['id']));
										$steps = ($o_query ? $o_query->result_array() : array());
										foreach($steps as $step) {
											if($skip_to_step_item) {
												if($step['sortnr'] < $skip_to_step_item['sortnr']){
													continue;
												}
											}

											$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
											$o_query = $o_main->db->query($s_sql, array($step['id']));
											$fees = ($o_query ? $o_query->result_array() : array());
											$step['fees'] = $fees;

											//renew case if step updated
											$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($case['id']));
											$case = $o_query ? $o_query->row_array() : array();

											$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = '".$o_main->db->escape_str($step['collecting_cases_process_id'])."' AND sortnr < '".$o_main->db->escape_str($step['sortnr'])."' ORDER BY sortnr DESC";
											$o_query = $o_main->db->query($s_sql);
											$previous_step = $o_query ? $o_query->row_array() : array();

											$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = '".$o_main->db->escape_str($step['collecting_cases_process_id'])."' AND sortnr > '".$o_main->db->escape_str($step['sortnr'])."' ORDER BY sortnr ASC";
											$o_query = $o_main->db->query($s_sql);
											$next_step = $o_query ? $o_query->row_array() : array();

											$forced = false;
											// if(isset($collecting_level_to_move_from)) {
											//     if(intval($previous_step['caselevel']) == 0){
											//         if($previous_step['collectinglevel'] == $collecting_level_to_move_from) {
											//             $forced = true;
											//         }
											//     }
											// }

											//collecting caselevels can't be forced to be moved to next level
											// if(intval($previous_step['caselevel']) == 1) {
											//     $forced = false;
											// }
											$log.= $forced." ";

											if(isset($case_step_to_move_to)){
												if($case_step_to_move_to == $step['id']) {
													$forced = true;
												}
											}
											$log.=$case_step_to_move_to." ";

											$lastStep = false;
											if($case['collecting_cases_process_step_id'] == $step['id'] && !$next_step){
												$lastStep = true;
											}
											if($lastStep){
												$cases_to_move[] = $case['id'];
											} else {
												$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? AND (objection_closed_date = '0000-00-00' OR objection_closed_date is null) ORDER BY created DESC";
												$o_query = $o_main->db->query($s_sql, array($case['id']));
												$activeObjections = ($o_query ? $o_query->result_array() : array());
												if(count($activeObjections) == 0) {
													if($case['due_date'] != "0000-00-00" && $case['due_date'] != "") {
														if($case['collecting_cases_process_step_id'] == $previous_step['id'] || $lastStep || intval($case['collecting_cases_process_step_id']) == 0 || $forced) {
															if(!in_array($case['id'], $casesCheckedTheStepIds)){
																$profile_value = $profile_values[$process['id']][$step['id']];
																$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? AND (objection_closed_date <> '0000-00-00' AND objection_closed_date is not null) ORDER BY objection_closed_date DESC";
																$o_query = $o_main->db->query($s_sql, array($case['id']));
																$closedObjection = ($o_query ? $o_query->row_array() : array());
																$objectionTime = 0;
																if($closedObjection) {
																	// $objectionTime = strtotime("+".($objection_after_days)." days", strtotime($closedObjection['objection_closed_date']));
																}
																$dueDateTime = strtotime($case['due_date']);
																$days_after_due_date = $step['days_after_due_date'];
																if($profile_value['days_after_due_date'] != "") {
																	$days_after_due_date = $profile_value['days_after_due_date'];
																}
																if($days_after_due_date != ""){
																	$correctDueDateTime = strtotime("+".($days_after_due_date)." days", $dueDateTime);
																} else {
																	$correctDueDateTime = $dueDateTime;
																}
																// if($objectionTime > $correctDueDateTime){
																// 	$correctDueDateTime = $objectionTime;
																// }

																if($correctDueDateTime < time() || $forced)
																{
																	$without_fee = 1;
																	//get dueDate for transactions;
																	// $dueDate = $case['due_date'];
																	$add_number_of_days_to_due_date = $step['add_number_of_days_to_due_date'];
																	if($profile_value['add_number_of_days_to_due_date'] != "") {
																		$add_number_of_days_to_due_date = $profile_value['add_number_of_days_to_due_date'];
																	}
																	$dueDate = date("Y-m-d", strtotime("+".$add_number_of_days_to_due_date." days", time()));

																	$noFeeError3 = true;
																	$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%'";
																	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
																	$fee_transactions = $o_query ? $o_query->result_array() : array();
																	if(count($fee_transactions) > 0) {
																		$noFeeError3 = false;
																	}

																	$error_with_currency = false;
																	$currency_rate = 1;
																	if($currencyName != "NOK") {
																		$error_with_currency = true;

																		$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/get_currency_rates.php';
																		if (file_exists($hook_file)) {
																			include $hook_file;
																			if (is_callable($run_hook)) {
																				$hook_result = $run_hook(array("creditor_id"=>$creditor['id']));
																				if(count($hook_result['currencyRates']) > 0){
																					$currencyRates = $hook_result['currencyRates'];
																					foreach($currencyRates as $currencyRate) {
																						if($currencyRate['symbol'] == $currencyName) {
																							$currency_rate = $currencyRate['rate'];
																							$error_with_currency = false;
																							break;
																						}
																					}
																				}
																			}
																		}
																	}
																	$stepFeeAlreadyAdded = false;
																	foreach($fee_transactions as $fee_transaction) {
																		$commentArray = explode("_",$fee_transaction['comment']);
																		if($commentArray[2] == "interest"){
																		$transactionType = "interest";
																		} else if($commentArray[2] == "reminderFee"){
																		$transactionType = "reminderFee";
																		} else if($commentArray[0] == "Rente"){
																			$transactionType = "interest";
																		} else {
																			$transactionType = "reminderFee";
																		}
																		if($transactionType == "interest") {
																			if(!$fee_transaction['transaction_reseted']) {
																				$hook_params = array(
																					'transaction_id' => $fee_transaction['id'],
																					'amount'=>$fee_transaction['amount']*(-1),
																					'dueDate'=>$dueDate,
																					'text'=>$commentArray[0],
																					'type'=>$transactionType,
																					'type_no'=>$type_no,
																					'accountNo'=>$commentArray[1],
																					'close'=> 1,
																					'username'=> $username,
																					'caseId'=>$case['id'],
																					'stepId'=>$step['id']
																				);
																				if($invoiceDifferentCurrency) {
																					$hook_params['currency'] = $currencyName;
																					$hook_params['currency_rate'] = $currency_rate;
																					$hook_params['currency_unit'] = 1;
																				}
																				$total_hook_params[] = $hook_params;
																			}
																		} else if($transactionType == "reminderFee") {
																			if($commentArray[3] == $step['id']) {
																				$stepFeeAlreadyAdded = true;
																			}
																		}

																	}
																	if($invoice['invoice_nr'] > 0) {
																		//reset fees that for some reason without link
																		$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id is null AND invoice_nr = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%'";
																		$o_query = $o_main->db->query($s_sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
																		$fee_transactions_without_link = $o_query ? $o_query->result_array() : array();
																		foreach($fee_transactions_without_link as $fee_transaction) {
																			$commentArray = explode("_",$fee_transaction['comment']);
																			if($commentArray[2] == "interest"){
																			$transactionType = "interest";
																			} else if($commentArray[2] == "reminderFee"){
																			$transactionType = "reminderFee";
																			} else if($commentArray[0] == "Rente"){
																			$transactionType = "interest";
																			} else {
																			$transactionType = "reminderFee";
																			}
																			if($transactionType == "interest" || $transactionType == "reminderFee") {
																				if(!$fee_transaction['transaction_reseted']) {
																					$hook_params = array(
																						'transaction_id' => $fee_transaction['id'],
																						'amount'=>$fee_transaction['amount']*(-1),
																						'dueDate'=>$dueDate,
																						'text'=>$commentArray[0],
																						'type'=>$transactionType,
																						'type_no'=>$type_no,
																						'accountNo'=>$commentArray[1],
																						'close'=> 1,
																						'username'=> $username,
																						'caseId'=>$case['id'],
																						'stepId'=>$step['id']
																					);
																					if($invoiceDifferentCurrency) {
																						$hook_params['currency'] = $currencyName;
																						$hook_params['currency_rate'] = $currency_rate;
																						$hook_params['currency_unit'] = 1;
																					}
																					$total_hook_params[] = $hook_params;
																				}
																			}
																		}
																	}

																	$fee_added = false;

																	if(!$case['doNotAddLateFee']) {
																		$doNotAddFee = $step['doNotAddFee'];
																		if($profile_value['doNotAddFee'] > 0){
																			$doNotAddFee = $profile_value['doNotAddFee'] - 1;
																		}
																		if(!$doNotAddFee) {
																			if($step['reminder_transaction_text'] != "") {

																				$without_fee = 0;
																				$s_sql = "SELECT * FROM collecting_cases_fee_transaction_log WHERE collectingcase_id = ? AND collecting_cases_process_step_id = ? AND type = 0";
																				$o_query = $o_main->db->query($s_sql, array($case['id'], $step['id']));
																				$collecting_cases_fee_transaction_log = $o_query ? $o_query->row_array() : array();
																				if(!$collecting_cases_fee_transaction_log) {
																					if(!$stepFeeAlreadyAdded) {
																						$amount = 0;
																						if(intval($profile_value['reminder_amount_type']) == 0){
																							if($profile_value['reminder_amount'] > 0){
																								$amount = $profile_value['reminder_amount'];
																							} else {
																								$amount = $step['reminder_amount'];
																							}
																						} else {
																							$amount = 0;
																							foreach($step['fees'] as $fee){
																								if($invoice['collecting_case_original_claim'] > $fee['mainclaim_from_amount']){
																									$amount = $fee['amount'];
																								}
																							}
																							if(count($profile_value['fees']) > 0) {
																								foreach($profile_value['fees'] as $fee){
																									if($invoice['collecting_case_original_claim'] > $fee['mainclaim_from_amount']){
																										$amount = $fee['amount'];
																									}
																								}
																							}
																						}
																						if($amount > 0){
																							$transaction_text = $step['reminder_transaction_text'];
																							if($profile_value['reminder_transaction_text'] != ""){
																								$transaction_text = $profile_value['reminder_transaction_text'];
																							}
																							$hook_params = array(
																								'transaction_id' => $invoice['id'],
																								'amount'=>round($amount/$currency_rate, 2),
																								'text'=>$transaction_text." ".date("d.m.Y"),
																								'dueDate'=>$dueDate,
																								'type'=>'reminderFee',
																								'type_no'=>$type_no,
																								'accountNo'=>$reminder_bookaccount,
																								'username'=> $username,
																								'caseId'=>$case['id'],
																								'stepId'=>$step['id']
																							);
																							if($invoiceDifferentCurrency) {
																								$hook_params['currency'] = $currencyName;
																								$hook_params['currency_rate'] = $currency_rate;
																								$hook_params['currency_unit'] = 1;
																							}

																							$total_hook_params[] = $hook_params;
																						}
																					} else {
																						$s_sql = "INSERT INTO collecting_cases_fee_transaction_log SET created = NOW(),
																						collectingcase_id = ?, collecting_cases_process_step_id = ?, type=?";
																						$o_query = $o_main->db->query($s_sql, array($case['id'], $step['id'], 0));
																					}
																				}
																			}
																		}
																	}
																	$doNotAddInterest = $step['doNotAddInterest'];
																	if($profile_value['doNotAddInterest'] > 0){
																		$doNotAddInterest = $profile_value['doNotAddInterest'] - 1;
																	}
																	if(!$doNotAddInterest) {
																		$without_fee = 0;
																		$s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_case_id = ? ";
																		$o_query = $o_main->db->query($s_sql, array($case['id']));

																		$currentClaimInterest = 0;
																		$interestArray = calculate_interest($invoice, $case);
																		$totalInterest = 0;
																		foreach($interestArray as $interest) {
																			$interestRate = $interest['rate'];
																			$interestAmount = $interest['amount'];
																			$interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
																			$interestTo = date("Y-m-d", strtotime($interest['dateTo']));

																			$s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
																			date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_case_id = '".$o_main->db->escape_str($case['id'])."'";
																			$o_query = $o_main->db->query($s_sql, array());
																			$totalInterest += $interestAmount;
																		}
																		if($totalInterest > 0) {
																			$hook_params = array(
																				'transaction_id' => $invoice['id'],
																				'amount'=>round($totalInterest/$currency_rate, 2),
																				'dueDate'=>$dueDate,
																				'text'=>$formText_Interest_output,
																				'type'=>'interest',
																				'type_no'=>$type_no,
																				'accountNo'=>$interest_bookaccount,
																				'username'=> $username,
																				'caseId'=>$case['id'],
																				'stepId'=>$step['id']
																			);
																			if($invoiceDifferentCurrency) {
																				$hook_params['currency'] = $currencyName;
																				$hook_params['currency_rate'] = $currency_rate;
																				$hook_params['currency_unit'] = 1;
																			}
																			$total_hook_params[] = $hook_params;
																		}
																	}
																	if(!$error_with_currency) {
																		$case['step'] = $step;
																		$case['lastStep'] = $lastStep;
																		$case['without_fee'] = $without_fee;
																		$case['profile_value'] = $profile_value;
																		$casesToContinueProcessing[] = $case;
																	} else {
																		echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_ErroConvertingCurrency_output." ".$case['id']."<br/>";
																	}
																	$casesCheckedTheStepIds[] = $case['id'];
																	break;
																} else {
																	if($manualProcessing && !in_array($case['id'], $casesToContinueProcessing)) {
																		echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_DueDateForProcessingIsNotReachedForCaseNr_output." ".$case['id']."<br/>";
																		break;
																	}
																}
															}
														}
													} else {
														echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_DueDateIsMissingForCase_output." ".$case['id']."<br/>";
														break;
													}
												}
											}
										}
									} else {
										echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_MissingProcess_output." ".$case['id']."<br/>";
									}
								} else {
									echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_MissingCurrency_output." ".$case['id']."<br/>";
								}
							} else {
								echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_MissingCurrency_output." ".$case['id']."<br/>";
							}
						} else {
							echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_InvoiceClosed_output." ".$case['id']."<br/>";
						}
		            }
				} else {
					echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_MissingProfile_output." ".$case['id']."<br/>";
				}
			}
	    } else {
	        echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_CaseAlreadyInProcessOfCreatingLetter_output."<br/>";
	    }
	}

	$transaction_errors = false;
	$collectingCasesWithoutError = array();
	if(count($total_hook_params) > 0) {
		$transaction_errors = true;
	    $hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_multiple_transactions.php';
	    if (file_exists($hook_file)) {
	        include $hook_file;
	        if (is_callable($run_hook)) {
				$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), createdBy= ?, creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
				$o_query = $o_main->db->query($s_sql, array($variables->loggID, $creditor['id'], 'total transaction started', $creditor_syncing_id));
	         	$connect_tries = 0;
				//try only 1 time to avoid duplicated
				$hook_result_no_error = true;
				$total_hook_params_chunked = array_chunk($total_hook_params, 20);
				$hook_results = array();
				foreach($total_hook_params_chunked as $total_hook_params_to_pass) {
					$hook_result = $run_hook($total_hook_params_to_pass);
					if(!$hook_result['result']) {
						$hook_results[] = $hook_result;
						$hook_result_no_error = false;

						if($hook_result['error'] != "" && $launchedFromProcessAllScript){
							echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_ErrorCreatingTransactions_output." - ". $hook_result['error']."<br/>";
						}
						// echo $hook_result['error'];
						if(strpos($hook_result['error'], "Office24Seven.Library.Economy.Accounting.DepartmentIsRequiredException") !== false) {
							echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_BookaccountRequiresDepartment_output."<br/>";

							$s_sql = "UPDATE creditor SET updated = NOW(), bookaccount_department_required = 1 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor['id']));
						}
						if(strpos($hook_result['error'], "Office24Seven.Library.Economy.Accounting.ProjectIsRequiredException") !== false) {
							echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_BookaccountRequiresProject_output."<br/>";

							$s_sql = "UPDATE creditor SET updated = NOW(), bookaccount_project_required = 1 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor['id']));
						}
						if($hook_result['error'] == -100){
							echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_FiscalYearIsNotSet_output."<br/>";
						}
					} else {
						foreach($total_hook_params_to_pass as $hook_params){
							if($hook_params['close']) {
								$s_sql = "UPDATE creditor_transactions SET transaction_reseted = 1 WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($hook_params['transaction_id']));
							}
						}	
					}
				}

	            if($hook_result_no_error) {
	                $transaction_errors = false;
					foreach($total_hook_params as $hook_params) {
						$fee_type = -1;
						if($hook_params['type'] == "interest") {
							$fee_type = 2;
						} else if($hook_params['type'] == "reminderFee") {
							$fee_type = 0;
						}
						if($fee_type >= 0) {
			                $s_sql = "INSERT INTO collecting_cases_fee_transaction_log SET created = NOW(),
			                collectingcase_id = ?, collecting_cases_process_step_id = ?, type=?";
			                $o_query = $o_main->db->query($s_sql, array($hook_params['caseId'], $hook_params['stepId'], $fee_type));
						}
					}
					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), createdBy = ?, creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
					$o_query = $o_main->db->query($s_sql, array($variables->loggID, $creditor['id'], 'total transaction ended', $creditor_syncing_id, $connect_tries));
	            } else {
					foreach($casesToContinueProcessing as $caseToContinueProcessing) {
						$hasTransactions = false;
						foreach($total_hook_params as $total_hook_param) {
							if($total_hook_param['caseId'] == $caseToContinueProcessing['id']){
								$hasTransactions = true;
								break;
							}
						}
						if(!$hasTransactions) {
							$collectingCasesWithoutError[] = $caseToContinueProcessing['id'];
						}
					}
					//sync the one that got added
					$fromProcessCases = true;
					include(__DIR__."/../import_scripts/import_cases2.php");

					$new_hook_params = array();
					foreach($total_hook_params as $total_hook_param) {
						$s_sql = "SELECT cc.* FROM collecting_cases cc WHERE id = ?";
					    $o_query = $o_main->db->query($s_sql, array($total_hook_param['caseId']));
					    $case = $o_query ? $o_query->row_array() : array();
						if($case){
							$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
							$invoice = ($o_query ? $o_query->row_array() : array());
							if($invoice) {
								$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%'";
								$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
								$fee_transactions = $o_query ? $o_query->result_array() : array();
								if($total_hook_param['stepId'] > 0){
									$transactionExists = false;
									foreach($fee_transactions as $fee_transaction){
									 	$commentArray = explode("_",$fee_transaction['comment']);
										if($commentArray[3] == $total_hook_param['stepId']) {
											$transactionExists = true;
										}
									}
									if(!$transactionExists) {
										$new_hook_params[] = $total_hook_param;
									} else {
										$fee_type = -1;
										if($total_hook_param['type'] == "interest") {
											$fee_type = 2;
										} else if($total_hook_param['type'] == "reminderFee") {
											$fee_type = 0;
										}
										if($fee_type >= 0) {
											$s_sql = "SELECT * FROM collecting_cases_fee_transaction_log WHERE collectingcase_id = ? AND collecting_cases_process_step_id = ? AND type = 0";
											$o_query = $o_main->db->query($s_sql, array($total_hook_param['caseId'], $total_hook_param['stepId']));
											$collecting_cases_fee_transaction_log = $o_query ? $o_query->row_array() : array();
											if(!$collecting_cases_fee_transaction_log) {
												$s_sql = "INSERT INTO collecting_cases_fee_transaction_log SET created = NOW(),
								                collectingcase_id = ?, collecting_cases_process_step_id = ?, type=?";
								                $o_query = $o_main->db->query($s_sql, array($total_hook_param['caseId'], $total_hook_param['stepId'], $fee_type));
											}
										}
										$collectingCasesWithoutError[] = $total_hook_param['caseId'];
									}
								} else {
									$new_hook_params[] = $total_hook_param;
								}
							}
						}
					}
					foreach($hook_results as $hook_result) {
						echo $hook_result['error']."<br/>";
					}
					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'total transaction failed: '.json_encode($hook_results)." ".json_encode($total_hook_params), $creditor_syncing_id, $connect_tries));
				}
	        }
	    }
	}
	if(!$transaction_errors || count($collectingCasesWithoutError) > 0) {
		if(count($casesToContinueProcessing) > 0){
			foreach($casesToContinueProcessing as $case) {
				$caseCorrectToProcess = true;
				if(count($collectingCasesWithoutError) > 0){
					$caseCorrectToProcess = false;
					if(in_array($case['id'], $collectingCasesWithoutError)){
						$caseCorrectToProcess = true;
					}
				}
				if($caseCorrectToProcess){
					$s_sql = "UPDATE collecting_cases SET updated = NOW(), create_letter = 1 WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($case['id']));
					
					$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1  WHERE collectingcase_id = ? AND creditor_id = ?";
					$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));

					$step = $case['step'];
					$lastStep = $case['lastStep'];
					$profile_value = $case['profile_value'];
					// $dueDate = $case['due_date'];

					// if($case['status'] != 3 && $case['collecting_cases_process_step_id'] != 0 && $lastStep){
					//
					// } else {
					$add_number_of_days_to_due_date = $step['add_number_of_days_to_due_date'];
					if($profile_value['add_number_of_days_to_due_date'] !=""){
						$add_number_of_days_to_due_date = $profile_value['add_number_of_days_to_due_date'];
					}
					$dueDate = date("Y-m-d", strtotime("+".$add_number_of_days_to_due_date." days", time()));
					// }

					$stopped_sql = "";

					if($case['status'] != 3 && $case['collecting_cases_process_step_id'] != 0 && $lastStep) {
						$s_sql = "UPDATE collecting_cases SET reminder_process_ended = NOW(), due_date = '".$o_main->db->escape_str($dueDate)."' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$casesToGenerate[] = $case['id'];
						
					} else {
						if($case['collecting_cases_process_step_id'] == 0) {
							$s_sql = "UPDATE collecting_cases SET  reminder_process_started = NOW() WHERE id = '".$o_main->db->escape_str($case['id'])."'";
							$o_query = $o_main->db->query($s_sql);
						}
						$s_sql = "UPDATE collecting_cases SET last_change_date_for_process = NOW(), due_date = '".$o_main->db->escape_str($dueDate)."', collecting_cases_process_step_id = '".$o_main->db->escape_str($step['id'])."'".$stopped_sql." WHERE id = '".$o_main->db->escape_str($case['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$casesToGenerate[] = $case['id'];
					}
				}
			}
			$fromProcessCases = true;
			include(__DIR__."/../import_scripts/import_cases2.php");
			$successfullyCreatedLetters = 0;
			$lettersForDownload = array();
			$failedLetters = array();
			$syncIssues = false;
			if($checkLinksCreated){
				$syncIssues = true;
				if($linksCreated && $totalImportedSuccessfully_links > 0){
					$syncIssues = false;
				}
			}
			if($failedMsg == "") {
				if(!$syncIssues) {
					
					$s_sql = "SELECT cc.* FROM collecting_cases cc WHERE cc.creditor_id = ? AND create_letter = 1 AND collecting_cases_process_step_id > 0 AND status = 0";
					$o_query = $o_main->db->query($s_sql, array($creditor['id']));
					$letter_ready_cases = $o_query ? $o_query->result_array() : array();
					foreach($letter_ready_cases as $letter_ready_case){
						if(!in_array($letter_ready_case['id'], $casesToGenerate)){
							$casesToGenerate[] = $letter_ready_case['id'];
						}
					}
					if(count($casesToGenerate) > 0) {
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

					    do{
							$code = generateRandomString(10);
							$code_check = null;
							$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
							$o_query = $o_main->db->query($s_sql, array($code));
							if($o_query){
								$code_check = $o_query->row_array();
							}
						} while($code_check != null);

					    foreach($casesToGenerate as $caseToGenerate) {
					        $s_sql = "SELECT * FROM collecting_cases WHERE id = ? AND create_letter = 1";
					        $o_query = $o_main->db->query($s_sql, array($caseToGenerate));
					        $caseData = ($o_query ? $o_query->row_array() : array());

					        $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
					        $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
					        $creditor = ($o_query ? $o_query->row_array() : array());

					        $result = generate_pdf($caseToGenerate);
					        if(count($result['errors']) > 0){
					            foreach($result['errors'] as $error){
					                echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
					            }
					        } else {
					            $successfullyCreatedLetters++;
								foreach($casesToContinueProcessing as $caseToContinueProcessing){
									if($caseToContinueProcessing['id'] == $caseToGenerate) {
										$s_sql = "UPDATE collecting_cases_claim_letter SET fees_status = ? WHERE id = ?";
					                    $o_query = $o_main->db->query($s_sql, array($caseToContinueProcessing['without_fee'], $result['item']['id']));
									}
								}
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
							
							$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
							WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' AND collectingcase_id = '".$o_main->db->escape_str($caseData['id'])."'";
							$o_query = $o_main->db->query($s_sql);
					    }
					}

					echo $successfullyCreatedLetters." ".$formText_LettersWereCreated_output."<br/>";

					if(count($lettersForDownload) > 0){
					    echo $formText_LettersForManualPrinting_output." <a href='".$extradomaindirroot."/modules/CollectingCaseClaimletter/output/includes/ajax.download.php?code=".$code."&ids=".implode(",",$lettersForDownload)."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>"."<br/>";
					}
				} else {
					echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_IssueWithSyncingPleaseTryLater_output."<br/>";
				}
			} else {
				echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_IssueWithSyncingPleaseTryLater_output."<br/>";
			}
		}
	} else {
		if(!$launchedFromProcessAllScript){
			echo ($fromMultiCreditorProcessing?$creditor['companyname']." ":'').$formText_ErrorCreatingTransactions_output."<br/>";
		}
	}
	//trigger reordering 							
	process_open_cases_for_tabs($creditorData['id'], 3);


	$s_sql = "UPDATE creditor SET last_process_date = NOW() WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
}
