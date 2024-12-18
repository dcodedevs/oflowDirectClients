<?php
require(__DIR__."/../creditor_functions.php");
require(__DIR__."/../fnc_move_transaction_to_collecting.php");
$nopreview = true;
if($set_preview){
	$nopreview = false;
}
$sql = "SELECT * FROM creditor WHERE content_status < 2 AND (choose_move_to_collecting_process = 1 OR (choose_progress_of_reminderprocess = 1 AND skip_reminder_go_directly_to_collecting > 0)) ORDER BY id";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();

$page = isset($_POST['page'])?$_POST['page']:1;
$perPage = 50;
$offset = ($page - 1)*$perPage;
$limit = $offset+$perPage;

$transactionCount = 0;
$totalTransactionCount = 0;
foreach($creditors as $creditor) {
	if(intval($creditor['onboarding_incomplete']) == 0){
		$reminder_bookaccount = 8070;
		$interest_bookaccount = 8050;
		if($creditor['reminder_bookaccount'] != ""){
			$reminder_bookaccount = $creditor['reminder_bookaccount'];
		}
		if($creditor['interest_bookaccount'] != ""){
			$interest_bookaccount = $creditor['interest_bookaccount'];
		}
		$creditor_move_to_collecting = $creditor['choose_move_to_collecting_process'];
		$sendToday = false;
		if($creditor['email_sending_day_choice_move'] == 0) {
			$sendToday = true;
		} else {
			$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 1";
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
		if($sendToday){			
			if($creditor['choose_move_to_collecting_process'] == 1 || ($creditor['choose_progress_of_reminderprocess'] == 1 && $creditor['skip_reminder_go_directly_to_collecting'] > 0)) {
					
				if($creditor['collecting_agreement_accepted_date'] != "" && $creditor['collecting_agreement_accepted_date'] != "0000-00-00 00:00:00") {	
					if($creditor['collecting_process_to_move_from_reminder'] == 0){
						$s_sql = "SELECT * FROM collecting_system_settings";
						$o_query = $o_main->db->query($s_sql);
						$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

						$collecting_process_to_move_from_reminder = $collecting_system_settings['default_collecting_process_to_move_from_reminder'];
					} else {
						$collecting_process_to_move_from_reminder = $creditor['collecting_process_to_move_from_reminder'];
					}
					$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($collecting_process_to_move_from_reminder));
					$collecting_process = ($o_query ? $o_query->row_array() : array());
					if($collecting_process) {
						$transactionsToBeMoved = array();

						$filters = array();
						$filters['order_field'] = 'debitor';
						$filters['order_direction'] = 0;
						$filters['list_filter'] = "notPayedConsiderCollectingProcess";

						$customerListNonProcessed = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);

						foreach($customerListNonProcessed as $case) {
							$mainClaim = 0;
							$interestAndFees = 0;
							$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($case['internalTransactionId']));
							$invoice = ($o_query ? $o_query->row_array() : array());

							$s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($s_sql, array($invoice['external_customer_id'], $invoice['creditor_id']));
							$debitorCustomer = $o_query ? $o_query->row_array() : array();
							
							$claim_transactions = array();
							if($invoice['link_id'] > 0){
								$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND invoice_nr = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
								$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['invoice_nr'], $invoice['creditor_id']));
								$claim_transactions = ($o_query ? $o_query->result_array() : array());
							}
							foreach($claim_transactions as $claim_transaction) {
								$interestAndFees+=$claim_transaction['amount'];
							}
							$restAmount = round($invoice['amount'], 2);
							$all_transaction_payments = array();
							if($invoice['link_id'] > 0){
								$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
								$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
								$all_transaction_payments = ($o_query ? $o_query->result_array() : array());
							}

							$transaction_payments = array();
							foreach($all_transaction_payments as $all_transaction_payment) {
								if(!in_array($all_transaction_payment['id'], $all_connected_transaction_ids)){
									$transaction_payments[] = $all_transaction_payment;
								}
							}

							$connected_transactions = array();
							$all_connected_transaction_ids = array($invoice['id']);
							if($invoice['link_id'] > 0 && ($creditor['checkbox_1'])) {
								$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
								$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['id']));
								$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
								foreach($connected_transactions_raw as $connected_transaction_raw) {
									if(strpos($connected_transaction_raw['comment'], '_') === false) {
										$connected_transactions[] = $connected_transaction_raw;
									}
								}
								foreach($connected_transactions as $connected_transaction){
									$all_connected_transaction_ids[] = $connected_transaction['id'];
								}
							}

							if(count($connected_transactions) == 0) {
								foreach($transaction_payments as $transaction_payment){
									$restAmount += round($transaction_payment['amount'], 2);
								}
							}
							$restAmount = round($restAmount, 2);
							$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];				
							$case_move_to_collecting = $case['choose_move_to_collecting_process'];
							$disabled = false;
							if($case_move_to_collecting == 0){
								if($customer_move_to_collecting == 0){
									$case_move_to_collecting = $creditor_move_to_collecting;
								} else {
									$case_move_to_collecting = $customer_move_to_collecting - 1;
								}
							} else {
								$case_move_to_collecting--;
							}
							if($case_move_to_collecting == 2) {
								$notSendInfos[] = array('info'=>$formText_CustomerMarkedNotMoveToCollecting_output);
								
								$disabled = true;
							}
							$case['debitor'] = $debitorCustomer;
							$correctAddress = false;
							if($debitorCustomer['paCity'] != "" && $debitorCustomer['paStreet'] != ""  && $debitorCustomer['paPostalNumber'] != "" ){
								$correctAddress = true;
							}
							if($correctAddress){
								if(!$disabled){
									if($restAmount > $system_settings['minimum_amount_move_to_collecting_company_case'] || $showAll) {
										if($transactionCount >= $offset && $transactionCount < $limit) {
											$transactionsToBeMoved[] = $case;
										}
										$transactionCount++;
									}
								}
							}
						}

						if(count($transactionsToBeMoved) > 0) {
							if(strtotime($creditor['lastImportedDate'])+30*60 < time()){
								$creditorsToSync[] = $creditor['id'];
								$creditorsToSyncFull[] = $creditor;
							}
						}
						if($nopreview) {
							$transactionsToBeMoved = array();
							foreach($customerListNonProcessed as $v_row) {
								if(in_array($v_row['internalTransactionId'], $_POST['transaction_ids'])) {
									$transactionsToBeMoved[] = $v_row;
								}
							}
							// $total_hook_params = array();
							// foreach($transactionsToBeMoved as $transaction) {
							// 	$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
							// 	$o_query = $o_main->db->query($s_sql, array($transaction['collectingcase_id']));
							// 	$case = ($o_query ? $o_query->row_array() : array());
							// 	if($case) {
							// 		$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ? AND creditor_id = ?";
							// 		$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
							// 		$invoice = $o_query ? $o_query->row_array() : array();
							// 		if($invoice){
							// 			$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND invoice_nr = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%'";
							// 			$o_query = $o_main->db->query($s_sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
							// 			$fee_transactions = $o_query ? $o_query->result_array() : array();

							// 			foreach($fee_transactions as $fee_transaction){
							// 				$commentArray = explode("_",$fee_transaction['comment']);
							// 				if($commentArray[2] == "interest"){
							// 					$transactionType = "interest";
							// 				} else if($commentArray[2] == "reminderFee"){
							// 					$transactionType = "reminderFee";
							// 				} else if($commentArray[0] == "Rente"){
							// 					$transactionType = "interest";
							// 				} else {
							// 					$transactionType = "reminderFee";
							// 				}
							// 				$hook_params = array(
							// 					'transaction_id' => $fee_transaction['id'],
							// 					'amount'=>$fee_transaction['amount']*(-1),
							// 					'dueDate'=>$dueDate,
							// 					'text'=>$commentArray[0],
							// 					'type'=>$transactionType,
							// 					'accountNo'=>$commentArray[1],
							// 					'close'=> 1
							// 				);

							// 				$total_hook_params[] = $hook_params;
							// 			}
							// 		}
							// 	}
							// }
							// $transaction_errors = false;
							// if(count($total_hook_params) > 0) {
							// 	$transaction_errors = true;
							//     $hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_multiple_transactions.php';
							//     if (file_exists($hook_file)) {
							//         include $hook_file;
							//         if (is_callable($run_hook)) {
							// 			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
							// 			$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'Move to collecting: total transaction started', $creditor_syncing_id));
							//          	$connect_tries = 0;
							// 			do {
							// 				$connect_tries++;
							// 				$hook_result = $run_hook($total_hook_params);
							// 				if($hook_result['result']){
							// 					break;
							// 				}
							// 			} while($connect_tries < 11);
							// 			$connect_tries--;
							//             if($hook_result['result']) {
							//                 $transaction_errors = false;
							// 				foreach($total_hook_params as $hook_params){
							// 					$fee_type = -1;
							// 					if($hook_params['type'] == "interest"){
							// 						$fee_type = 2;
							// 					} else if($hook_params['type'] == "reminderFee") {
							// 						$fee_type = 0;
							// 					}
							// 					if($fee_type >= 0) {
							// 		                $s_sql = "INSERT INTO collecting_cases_fee_transaction_log SET created = NOW(),
							// 		                collectingcase_id = ?, collecting_cases_process_step_id = ?, type=?";
							// 		                $o_query = $o_main->db->query($s_sql, array($hook_params['caseId'], $hook_params['stepId'], $fee_type));
							// 					}
							// 				}
							// 				$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
							// 				$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'Move to collecting: total transaction ended', $creditor_syncing_id, $connect_tries));
							//             } else {
							// 				// echo $hook_result['error'];
							// 				if(strpos($hook_result['error'], "Office24Seven.Library.Economy.Accounting.DepartmentIsRequiredException") !== false) {
							// 					echo $formText_BookaccountRequiresDepartment_output."<br/>";

							// 					$s_sql = "UPDATE creditor SET updated = NOW(), bookaccount_department_required = 1 WHERE id = ?";
							// 					$o_query = $o_main->db->query($s_sql, array($creditor['id']));
							// 				}
							// 				if(strpos($hook_result['error'], "Office24Seven.Library.Economy.Accounting.ProjectIsRequiredException") !== false) {
							// 					echo $formText_BookaccountRequiresProject_output."<br/>";

							// 					$s_sql = "UPDATE creditor SET updated = NOW(), bookaccount_project_required = 1 WHERE id = ?";
							// 					$o_query = $o_main->db->query($s_sql, array($creditor['id']));
							// 				}

							// 				$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
							// 				$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'Move to collecting: total transaction failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
							// 			}
							//         }
							//     }
							// }
							if(count($transactionsToBeMoved) > 0) {
								$transactionsMoved = 0;
								if(!$transaction_errors) {
									// $fromProcessCases = true;
									// include(__DIR__."/../import_scripts/import_cases2.php");
									foreach($transactionsToBeMoved as $transaction) {
										$sql = "SELECT customer.* FROM customer
										WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
										$o_query = $o_main->db->query($sql, array($transaction['external_customer_id'], $creditor['id']));
										$debitorData = $o_query ? $o_query->row_array() : array();

										$customer_type_collect_debitor = $debitorData['customer_type_collect'];
										if($debitorData['customer_type_collect_addition'] > 0){
											$customer_type_collect_debitor = $debitorData['customer_type_collect_addition'] - 1;
										}

										$processId = $collecting_process['id'];
										if($transaction['collectingProcessToMoveTo'] > 0){
											$processId = $transaction['collectingProcessToMoveTo'];
										}
										$v_return = move_transaction_to_collecting($transaction["internalTransactionId"], $processId, $username, $forceMove);
										if($v_return['status']) {
											$transactionsMoved++;
										} else {
											foreach($v_return['error'] as $error){
												echo $error."<br/>";
											}
										}
									}
									echo $transactionsMoved . " ".$formText_TransactionsMoved_output."</br>";
								} else {
									echo $formText_ErrorResettingFees;
								}
							}
						} else {
							$return_data[$creditor['id']] = $transactionsToBeMoved;
						}
					} else {
						echo $creditor['companyname']." ".$formText_MissingCollectingProcess."<br/>";
					}
				} else {			
					echo $creditor['companyname']." ".$formText_CollectingAgreementNotSigned_output."<br/>";
				}
			}
		}
	}
}
$totalPages = ceil($transactionCount / $perPage);

?>
