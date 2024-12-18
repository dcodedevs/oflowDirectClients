<?php
include(__DIR__."/../../../../CollectingCases/output/includes/fnc_calculate_interest.php");
include(__DIR__."/../../../../CollectingCases/output/includes/fnc_generate_pdf.php");
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

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
$creditorCustomer = $o_query ? $o_query->row_array() : array();

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

$objection_after_days = intval($system_settings['default_days_after_closed_objection_to_process']);
if($creditor['days_after_closed_objection_to_process'] != NULL){
    $objection_after_days = $creditor['days_after_closed_objection_to_process'];
}
if(intval($creditor['choose_progress_of_reminderprocess']) == 0){
    $creditor['choose_how_to_create_collectingcase'] = 0;
}
$onhold_sql = " AND (cc.onhold_by_creditor is null OR cc.onhold_by_creditor = 0)";
if($skip_to_step > 0){
    $onhold_sql = "";
}
$collectingcase_status_sql = " AND ct.open = 1";

if(isset($collecting_case_id)){
    if(!is_array($collecting_case_id)){
        $collecting_case_id = array($collecting_case_id);
    }
    $s_sql = "SELECT cc.* FROM collecting_cases cc JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id WHERE cc.creditor_id = ? ".$collectingcase_status_sql.$onhold_sql." AND cc.id IN (".implode(",", $collecting_case_id).")";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    $cases = $o_query ? $o_query->result_array() : array();
} else {
    $s_sql = "SELECT cc.* FROM collecting_cases cc JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id WHERE cc.creditor_id = ? ".$collectingcase_status_sql.$onhold_sql."";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    $cases = $o_query ? $o_query->result_array() : array();
}
if($skip_to_step > 0){
    $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($skip_to_step));
    $skip_to_step_item = ($o_query ? $o_query->row_array() : array());
}
$casesToGenerate = array();

$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW()";
$o_query = $o_main->db->query($s_sql, array($creditor['id']));
if($o_query){
	$creditor_syncing_id = $o_main->db->insert_id();
}
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

		if($case['reminder_profile_id'] > 0){
			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($case['reminder_profile_id']));
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
	                if($process){
	                    $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
	                    $o_query = $o_main->db->query($s_sql, array($process['id']));
	                    $steps = ($o_query ? $o_query->result_array() : array());
	                    foreach($steps as $step) {
	                        if($skip_to_step_item) {
	                            if($step['sortnr'] < $skip_to_step_item['sortnr']){
	                                continue;
	                            }
	                        }
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
	                        $s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? AND (objection_closed_date = '0000-00-00' OR objection_closed_date is null) ORDER BY created DESC";
	                        $o_query = $o_main->db->query($s_sql, array($case['id']));
	                        $activeObjections = ($o_query ? $o_query->result_array() : array());
	                        if(count($activeObjections) == 0) {
	                            if($case['collecting_cases_process_step_id'] == $previous_step['id'] || $lastStep || intval($case['collecting_cases_process_step_id']) == 0 || $forced) {
									$profile_value = $profile_values[$process['id']][$step['id']];
	                                $s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? AND (objection_closed_date <> '0000-00-00' AND objection_closed_date is not null) ORDER BY objection_closed_date DESC";
	                                $o_query = $o_main->db->query($s_sql, array($case['id']));
	                                $closedObjection = ($o_query ? $o_query->row_array() : array());
	                                $objectionTime = 0;
	                                if($closedObjection) {
	                                    $objectionTime = strtotime("+".($objection_after_days)." days", strtotime($closedObjection['objection_closed_date']));
	                                }

	                                $dueDateTime = strtotime($case['due_date']);
	                                if($step['days_after_due_date'] != ""){
	                                    $correctDueDateTime = strtotime("+".($step['days_after_due_date'])." days", $dueDateTime);
	                                } else {
	                                    $correctDueDateTime = $dueDateTime;
	                                }
	                                if($objectionTime > $correctDueDateTime){
	                                    $correctDueDateTime = $objectionTime;
	                                }

	                                if($correctDueDateTime < time() || $forced)
	                                {
	                                    $s_sql = "UPDATE collecting_cases SET updated = NOW(), create_letter = 1 WHERE id = ?";
	                                    $o_query = $o_main->db->query($s_sql, array($case['id']));
	                                    $debtCollectionTableName = "";
	                                    $claimTypeForType3 = "";
	                                    $claimTypeForType2 = "";
	                                    if($creditorCustomer) {
											$customer_type_collect_creditor = $creditorCustomer['customer_type_collect'];
											if($creditorCustomer['customer_type_collect_addition'] > 0){
												$customer_type_collect_creditor = $creditorCustomer['customer_type_collect_addition'] - 1;
											}
	                                        if(intval($customer_type_collect_creditor) == 0) {
	                                            if($creditor['vat_deduction']){
	                                                $debtCollectionTableName = "debtcollectionfeecompanycreditorwithvatdeduct";
	                                                $claimTypeForType3 = '6';
	                                            } else {
	                                                $debtCollectionTableName = "debtcollectionfeecompanycreditorwithoutvatdeduct";
	                                                $claimTypeForType3 = '7';
	                                            }
	                                        } else if($customer_type_collect_creditor == 1) {
	                                            if($creditor['vat_deduction']){
	                                                $debtCollectionTableName = "debtcollectionfeepersoncreditorwithvatdeduct";
	                                                $claimTypeForType3 = '4';
	                                            } else {
	                                                $debtCollectionTableName = "debtcollectionfeepersoncreditorwithoutvatdeduct";
	                                                $claimTypeForType3 = '5';
	                                            }
	                                        }
	                                    }

	                                    $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
	                                    $o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
	                                    $invoice = ($o_query ? $o_query->row_array() : array());

	                                    // $articleForType3 = array();
	                                    // if($debtCollectionTableName != ""){
	                                    //     if($step['claim_type_3_article'] > 0) {
	                                    //         $baseAmount = $invoice['collecting_case_original_claim'];
	                                    //
	                                    //         $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
	                                    //         WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 1)";
	                                    //         $o_query = $o_main->db->query($sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
	                                    //         $payments = $o_query ? $o_query->result_array() : array();
	                                    //         foreach($payments as $payment) {
	                                    //             $baseAmount += $payment['amount'];
	                                    //         }
	                                    //
	                                    //         $s_sql = "SELECT * FROM ".$debtCollectionTableName." WHERE amountFrom < ? ORDER BY amountFrom DESC";
	                                    //         $o_query = $o_main->db->query($s_sql, array($baseAmount));
	                                    //         $articleForType3 = $o_query ? $o_query->row_array() : array();
	                                    //     }
	                                    // }

	                                    //get dueDate for transactions;
	                                    $dueDate = $case['due_date'];

	                                    if($case['status'] != 3 && $case['collecting_cases_process_step_id'] != 0 && $lastStep){

	                                    } else {

	                                        $dueDate = date("Y-m-d", strtotime("+".$step['add_number_of_days_to_due_date']." days", time()));
	                                    }

	                                    $noFeeError3 = true;
	                                    $s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%'";
	                                    $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	                                    $fee_transactions = $o_query ? $o_query->result_array() : array();
	                                    if(count($fee_transactions) > 0) {
	                                        $noFeeError3 = false;
	                                    }
										$interestFeeCount = 0;
	                                    $noFeeError3count = 0;
	                                    foreach($fee_transactions as $fee_transaction){
	                                        $commentArray = explode("_",$fee_transaction['comment']);
	                                        if($commentArray[1] == $reminder_bookaccount){
	                                            $transactionType = "reminderFee";
	                                        } else if($commentArray[1] == $interest_bookaccount){
	                                            $transactionType = "interest";
	                                        }
	                                        if($transactionType == "interest") {
												$interestFeeCount++;
	                                            $hook_params = array(
	                                                'transaction_id' => $fee_transaction['id'],
	                                                'amount'=>$fee_transaction['amount']*(-1),
	                                                'dueDate'=>$dueDate,
	                                                'text'=>$commentArray[0],
	                                                'type'=>$transactionType,
	                                                'accountNo'=>$commentArray[1],
	                                                'close'=> 1,
													'username'=> $username
	                                            );

	                                            $hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
	                                            if (file_exists($hook_file)) {
	                                                include $hook_file;
	                                                if (is_callable($run_hook)) {
														$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
														$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset interest started', $creditor_syncing_id));

														$connect_tries = 0;
														do {
															$connect_tries++;
															$hook_result = $run_hook($hook_params);
															if($hook_result['result']){
																break;
															}
														} while($connect_tries < 11);
														$connect_tries--;
	                                                    if($hook_result['result']){
	                                                        $noFeeError3count++;
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
															$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset interest finished', $creditor_syncing_id, $connect_tries));
	                                                    } else {
	                                                        // var_dump("deleteError".$hook_result['error']);
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
															$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset interest failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
	                                                    }
	                                                }
	                                            }
	                                        }
	                                    }
	                                    if($noFeeError3count == $interestFeeCount) {
	                                        $noFeeError3 = true;
	                                    }
	                                    if($noFeeError3){
	                                        $noFeeError = true;
	                                        if(!$case['doNotAddLateFee']) {
	                                            if($step['reminder_transaction_text'] != "") {
	                                                $s_sql = "SELECT * FROM collecting_cases_fee_transaction_log WHERE collectingcase_id = ? AND collecting_cases_process_step_id = ? AND type = 0";
	                                                $o_query = $o_main->db->query($s_sql, array($case['id'], $step['id']));
	                                                $collecting_cases_fee_transaction_log = $o_query ? $o_query->row_array() : array();
	                                                if(!$collecting_cases_fee_transaction_log) {
														if($profile_value['reminder_amount'] > 0){
															$amount = $profile_value['reminder_amount'];
														} else {
	 	                                                    $amount = $step['reminder_amount'];
														}
	                                                    // $s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND collecting_cases_process_step_id = ? AND type = 0";
	                                                    // $o_query = $o_main->db->query($s_sql, array($creditor['id'], $step['id']));
	                                                    // $reminder_customized_fee = $o_query ? $o_query->row_array() : array();
	                                                    // if($reminder_customized_fee){
	                                                    //     if($reminder_customized_fee['amount_company'] != null && $debitorCustomer['customer_type_collect'] == 0) {
	                                                    //         $amount = $reminder_customized_fee['amount_company'];
	                                                    //     } else if($reminder_customized_fee['amount_person'] != null && $debitorCustomer['customer_type_collect'] == 1) {
	                                                    //         $amount = $reminder_customized_fee['amount_company'];
	                                                    //     }
	                                                    // }
														$transaction_text = $step['reminder_transaction_text'];
														if($profile_value['reminder_transaction_text'] != ""){
															$transaction_text = $profile_value['reminder_transaction_text'];
														}
	                                                    $noFeeError = false;
	                                                    $hook_params = array(
	                                                        'transaction_id' => $invoice['id'],
	                                                        'amount'=>$amount,
	                                                        'text'=>$transaction_text,
	                                                        'dueDate'=>$dueDate,
	                                                        'type'=>'reminderFee',
	                                                        'accountNo'=>$reminder_bookaccount,
															'username'=> $username
	                                                    );

	                                                    $hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
	                                                    if (file_exists($hook_file)) {
	                                                        include $hook_file;
	                                                        if (is_callable($run_hook)) {
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
																$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reminder transaction started', $creditor_syncing_id));
																$connect_tries = 0;
																do {
																	$connect_tries++;
																	$hook_result = $run_hook($hook_params);
																	if($hook_result['result']){
																		break;
																	}
																} while($connect_tries < 11);
																$connect_tries--;
	                                                            if($hook_result['result']) {
	                                                                $noFeeError = true;
	                                                                $s_sql = "INSERT INTO collecting_cases_fee_transaction_log SET created = NOW(),
	                                                                collectingcase_id = ?, collecting_cases_process_step_id = ?, type=0";
	                                                                $o_query = $o_main->db->query($s_sql, array($case['id'], $step['id']));

																	$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																	$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reminder transaction  finished', $creditor_syncing_id, $connect_tries));
			                                                    } else {
			                                                        // var_dump("deleteError".$hook_result['error']);
																	$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																	$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reminder transaction  failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
			                                                    }
	                                                        }
	                                                    }
	                                                }
	                                            }
	                                        }
	                                        // $noFeeError2 = true;
	                                        // if(!$case['doNotAddDebtCollectionFee']) {
	                                        //     if(intval($step['claim_type_3_article']) > 0) {
	                                        //         $noFeeError2 = false;
	                                        //         if($articleForType3) {
	                                        //             if($step['claim_type_3_article'] == 1) {
	                                        //                 $amount = $articleForType3['lightFee'];
	                                        //             } else {
	                                        //                 $amount = $articleForType3['heavyFee'];
	                                        //             }
	                                        //             $hook_params = array(
	                                        //                 'transaction_id' => $invoice['id'],
	                                        //                 'amount'=>$amount,
	                                        //                 'dueDate'=>$dueDate,
	                                        //                 'text'=>$articleForType3['articleText'],
	                                        //                 'type'=>'reminderFee',
	                                        //                 'accountNo'=>$reminder_bookaccount
	                                        //             );
	                                        //
	                                        //             $hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
	                                        //             if (file_exists($hook_file)) {
	                                        //                 include $hook_file;
	                                        //                 if (is_callable($run_hook)) {
	                                        //                     $hook_result = $run_hook($hook_params);
	                                        //                     if($hook_result['result']){
	                                        //                         $noFeeError2 = true;
	                                        //                     } else {
	                                        //                         // var_dump($hook_result['error']);
	                                        //                     }
	                                        //                 }
	                                        //             }
	                                        //             // var_dump($hook_result);
	                                        //
	                                        //         }
	                                        //     }
	                                        // }
	                                        $noInterestError = false;
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
	                                                'amount'=>$totalInterest,
	                                                'dueDate'=>$dueDate,
	                                                'text'=>$formText_Interest_output,
	                                                'type'=>'interest',
	                                                'accountNo'=>$interest_bookaccount,
													'username'=> $username
	                                            );

	                                            $hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
	                                            if (file_exists($hook_file)) {
	                                                include $hook_file;
	                                                if (is_callable($run_hook)) {
														$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
														$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'interest transaction started', $creditor_syncing_id));
	                                                 	$connect_tries = 0;
														do {
															$connect_tries++;
															$hook_result = $run_hook($hook_params);
															if($hook_result['result']){
																break;
															}
														} while($connect_tries < 11);
														$connect_tries--;
	                                                    if($hook_result['result']){
	                                                        $noInterestError = true;
	                                                        $s_sql = "INSERT INTO collecting_cases_fee_transaction_log SET created = NOW(),
	                                                        collectingcase_id = ?, collecting_cases_process_step_id = ?, type=2";
	                                                        $o_query = $o_main->db->query($s_sql, array($case['id'], $step['id']));
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
															$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'interest transaction ended', $creditor_syncing_id, $connect_tries));
	                                                    } else {
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
															$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'interest transaction failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
														}
	                                                }
	                                            }
	                                            // var_dump($hook_result);
	                                        } else {
	                                            $noInterestError = true;
	                                        }

	                                        if($noFeeError && $noInterestError) {
	                                            // var_dump("all good");
	                                            $stopped_sql = "";
	                                            // if($step['status_id'] == 4 || $step['status_id'] == 2) {
	                                            //     $stopped_sql = ", stopped_date = NOW()";
	                                            // }

	                                            if($case['status'] != 3 && $case['collecting_cases_process_step_id'] != 0 && $lastStep){
	                                                $s_sql = "UPDATE collecting_cases SET reminder_process_ended = NOW() WHERE id = '".$o_main->db->escape_str($case['id'])."'";
	                                                $o_query = $o_main->db->query($s_sql);
	                                                $casesToGenerate[] = $case['id'];
	                                                // if(intval($creditor['choose_how_to_create_collectingcase']) == 1 || $manualProcessing) {
	                                                //     $s_sql = "UPDATE collecting_cases SET last_change_date_for_process = NOW(), collecting_process_started = NOW(), collecting_cases_process_step_id = '0'".$stopped_sql." WHERE id = '".$o_main->db->escape_str($case['id'])."'";
	                                                //     $o_query = $o_main->db->query($s_sql);
	                                                // }
	                                            } else {
	                                                if($case['collecting_cases_process_step_id'] == 0) {
	                                                    $s_sql = "UPDATE collecting_cases SET  reminder_process_started = NOW() WHERE id = '".$o_main->db->escape_str($case['id'])."'";
	                                                    $o_query = $o_main->db->query($s_sql);
	                                                }
	                                                $s_sql = "UPDATE collecting_cases SET last_change_date_for_process = NOW(), due_date = '".$o_main->db->escape_str($dueDate)."', collecting_cases_process_step_id = '".$o_main->db->escape_str($step['id'])."'".$stopped_sql." WHERE id = '".$o_main->db->escape_str($case['id'])."'";
	                                                $o_query = $o_main->db->query($s_sql);
	                                                $casesToGenerate[] = $case['id'];

	                                            }
	                                        } else {
	                                            echo $formText_ErrorCreatingTransactions_output." ".$case['id']."<br/>";
	                                            break;
	                                        }
	                                    } else {
	                                        echo $formText_ErrorCreatingTransactions_output." ".$case['id']."<br/>";
	                                        break;
	                                    }
	                                } else {
	                                    if($manualProcessing && !in_array($case['id'], $casesToGenerate)) {
	                                        echo $formText_DueDateForProcessingIsNotReachedForCaseNr_output." ".$case['id']."<br/>";
	                                        break;
	                                    }
	                                }
	                            }
	                        }
	                    }
	                } else {
	                    echo $formText_MissingProcess_output." ".$case['id']."<br/>";
	                }
	            }
			} else {
				echo $formText_MissingProfile_output." ".$case['id']."<br/>";
			}
		}
        // } else {
        //     include("handle_cases_payment_plan.php");
        // }
    } else {
        echo $formText_CaseAlreadyInProcessOfCreatingLetter_output."<br/>";
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
if(!$syncIssues){
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

	        $s_sql = "SELECT creditor.*, customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
	        $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
	        $creditor = ($o_query ? $o_query->row_array() : array());

	        $result = generate_pdf($caseToGenerate);
	        if(count($result['errors']) > 0){
	            foreach($result['errors'] as $error){
	                echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
	            }
	        } else {
	            $successfullyCreatedLetters++;
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
	echo $successfullyCreatedLetters." ".$formText_LettersWereCreated_output."<br/>";

	if(count($lettersForDownload) > 0){
	    echo $formText_LettersForManualPrinting_output." <a href='".$extradomaindirroot."/modules/CollectingCaseClaimletter/output/includes/ajax.download.php?code=".$code."&ids=".implode(",",$lettersForDownload)."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>"."<br/>";
	}
} else {
	echo $formText_IssueWithSyncingPleaseTryLater_output;
}
$s_sql = "UPDATE creditor SET last_process_date = NOW() WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor['id']));
