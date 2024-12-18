<?php
if(!function_exists("proc_tverrsum")){
	function proc_tverrsum($tall){
		return array_sum(str_split($tall));
	}
}
if(!function_exists("proc_mod10")){
	function proc_mod10($kid_u){
	    $siffer = str_split(strrev($kid_u));
	    $sum = 0;

	    for($i=0; $i<count($siffer); ++$i) $sum += proc_tverrsum(( $i & 1 ) ? $siffer[$i] * 1 : $siffer[$i] * 2);


		$controlnumber = ($sum==0) ? 0 : 10 - substr($sum, -1);
		if ($controlnumber == 10) $controlnumber = 0;
	    return $controlnumber;
	}
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
if(!function_exists("move_transaction_to_collecting")){
	function move_transaction_to_collecting($transaction_id, $process_id, $username = 'autoprocess', $force_move = false){
		global $o_main;
		include(__DIR__."/../../output/languagesOutput/default.php");
		if(is_file(__DIR__."/../../output/languagesOutput/no.php")) {
			include(__DIR__."/../../output/languagesOutput/no.php");
		}
		$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($process_id));
		$collectingProcess = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($transaction_id));
		$transaction = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($transaction['collectingcase_id']));
		$case = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM collecting_system_settings";
		$o_query = $o_main->db->query($s_sql);
		$system_settings = ($o_query ? $o_query->row_array() : array());	

		$time_log = array();
		$v_return = array();
		if($collectingProcess) {
		    if($transaction && intval($transaction['collecting_company_case_id']) == 0) {		

				$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
				$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
				$creditor = ($o_query ? $o_query->row_array() : array());

				$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW()";
				$o_query = $o_main->db->query($s_sql, array($creditor['id']));
				if($o_query){
					$creditor_syncing_id = $o_main->db->insert_id();
				}

				$connected_transactions = array();
				$all_connected_transaction_ids = array($transaction['id']);
				if($transaction['link_id'] > 0 && ($creditor['checkbox_1'])) {
					$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
					$o_query = $o_main->db->query($s_sql, array($transaction['link_id'], $transaction['id']));
					$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
					foreach($connected_transactions_raw as $connected_transaction_raw){
						if(strpos($connected_transaction_raw['comment'], '_') === false){
							$connected_transactions[] = $connected_transaction_raw;
						}
					}
					foreach($connected_transactions as $connected_transaction){
						$all_connected_transaction_ids[] = $connected_transaction['id'];
					}
				}

				$restAmount = $transaction['amount'];

				$all_transaction_payments = array();
				if($transaction['link_id'] > 0) {
					$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
					$o_query = $o_main->db->query($s_sql, array($transaction['link_id'], $transaction['creditor_id']));
					$all_transaction_payments = ($o_query ? $o_query->result_array() : array());
				}

				$transaction_payments = array();
				foreach($all_transaction_payments as $all_transaction_payment) {
					if(!in_array($all_transaction_payment['id'], $all_connected_transaction_ids)){
						$transaction_payments[] = $all_transaction_payment;
					}
				}

				if(count($connected_transactions) == 0) {
					foreach($transaction_payments as $transaction_payment){
						$restAmount += $transaction_payment['amount'];
					}
				}

				if($restAmount > $system_settings['minimum_amount_move_to_collecting_company_case']) {
					if($creditor['collecting_agreement_accepted_date'] != "" && $creditor['collecting_agreement_accepted_date'] != "0000-00-00 00:00:00" || $force_move) {
						$s_sql = "SELECT customer.* FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
						$o_query = $o_main->db->query($s_sql, array($transaction['external_customer_id'], $transaction['creditor_id']));
						$customer = ($o_query ? $o_query->row_array() : array());

						$currencyName = "";
						$invoiceDifferentCurrency = false;
						if($transaction['currency'] != ""){
							if($transaction['currency'] == 'LOCAL') {
								$currencyName = trim($creditor['default_currency']);
							} else {
								$currencyName = trim($transaction['currency']);
							}
						}
						$differentCurrency = 0;
						$sql_company_currency_sql = "";
						if($currencyName != "NOK") {
							$sql_company_currency_sql = ", currency = 1, currency_name = '".$o_main->db->escape_str($currencyName)."'";
							$differentCurrency = 1;
						}
						$createCase = false;
						if($differentCurrency) {
							$s_sql = "SELECT * FROM collecting_company_cases WHERE creditor_id = ? AND debitor_id = ? AND collecting_process_id = ?
							AND (collecting_cases_process_step_id is null OR collecting_cases_process_step_id = 0)
							AND IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'
							AND currency = 1 AND currency_name = '".$o_main->db->escape_str($currencyName)."'  AND content_status < 2";
							$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id'], $customer['id'], $collectingProcess['id']));
							$notStartedCollectingCase = ($o_query ? $o_query->row_array() : array());
						} else {
							$s_sql = "SELECT * FROM collecting_company_cases WHERE content_status < 2 AND creditor_id = ? AND debitor_id = ? AND collecting_process_id = ? AND (collecting_cases_process_step_id is null OR collecting_cases_process_step_id = 0) AND IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'";
							$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id'], $customer['id'], $collectingProcess['id']));
							$notStartedCollectingCase = ($o_query ? $o_query->row_array() : array());
						}
						if($notStartedCollectingCase){
							$col_company_case_id = $notStartedCollectingCase['id'];
						} else {
							$createCase = true;
						}

						$reminder_bookaccount = 8070;
						$interest_bookaccount = 8050;
						if($creditor['reminder_bookaccount'] != ""){
							$reminder_bookaccount = $creditor['reminder_bookaccount'];
						}
						if($creditor['interest_bookaccount'] != ""){
							$interest_bookaccount = $creditor['interest_bookaccount'];
						}
						$noFeeError3 = true;
						$needSyncing = false;
						if($case) {
							$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
							$invoice = $o_query ? $o_query->row_array() : array();
							if($invoice){
								$time_log['checks_done']=microtime();
								$fee_transactions = array();
								if($invoice['link_id'] > 0){
									$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%')";
									$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
									$fee_transactions = $o_query ? $o_query->result_array() : array();
								}
								if(count($fee_transactions) > 0) {
									$noFeeError3 = false;
								}
								$noFeeError3count = 0;
								$time_log['fee_to_reset']=$fee_transactions;
								$time_log['fees_reset_started']=microtime();
								foreach($fee_transactions as $fee_transaction){
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
									if(!$fee_transaction['transaction_reseted']) {
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

										$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
										if (file_exists($hook_file)) {
											include $hook_file;
											if (is_callable($run_hook)) {
												$hook_result = $run_hook($hook_params);
												if($hook_result['result']){
													$noFeeError3count++;
													if($hook_params['close']) {
														$s_sql = "UPDATE creditor_transactions SET transaction_reseted = 1 WHERE id = ?";
														$o_query = $o_main->db->query($s_sql, array($hook_params['transaction_id']));
													}
													$needSyncing = true;
												} else {
													// var_dump("deleteError".$hook_result['error']);
												}
											}
										}
									}
								}
								$time_log['fees_parameter']=$hook_params;
								$time_log['fees_done']=$hook_result;
								$time_log['fees_reset_stopped']=microtime();

								if($noFeeError3count == count($fee_transactions)){
									$noFeeError3 = true;
								}
							}
						}
						if($noFeeError3) {
							$date_sql = ", collecting_case_created_date = NOW()";

							$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = ? AND warning_level = 1 ORDER BY id";
							$o_query = $o_main->db->query($s_sql, array($collectingProcess['id']));
							$warning_process_steps = ($o_query ? $o_query->result_array() : array());

							if(count($warning_process_steps) > 0){
								$date_sql = ", warning_case_created_date = NOW()";
							}
							$time_log['create_case_started']=microtime();
							if($createCase) {
								$s_sql = "INSERT INTO collecting_company_cases SET
								created = now(),
								createdBy='".$o_main->db->escape_str($username)."',
								creditor_id='".$o_main->db->escape_str($transaction['creditor_id'])."',
								debitor_id='".$o_main->db->escape_str($customer['id'])."',
								collecting_process_id = '".$o_main->db->escape_str($collectingProcess['id'])."'".$date_sql.$sql_company_currency_sql;
								$o_query = $o_main->db->query($s_sql);
								if($o_query) {
									$col_company_case_id =  $o_main->db->insert_id();

									$kidNumber = generate_case_kidnumber($creditor['id'], $col_company_case_id);
									$s_sql = "UPDATE collecting_company_cases SET
									kid_number = '".$o_main->db->escape_str($kidNumber)."'
									WHERE id = '".$o_main->db->escape_str($col_company_case_id)."'";
									$o_query = $o_main->db->query($s_sql);
								}
							}
							if($col_company_case_id > 0){
								$s_sql = "UPDATE creditor_transactions SET
								updated = now(),
								updatedBy= ?,
								collecting_company_case_id= ?
								WHERE id = ?";
								$o_main->db->query($s_sql, array($username, $col_company_case_id, $transaction['id']));
								
								if($case){
									$s_sql = "UPDATE collecting_cases SET
									updated = now(),
									updatedBy='".$o_main->db->escape_str($username)."',
									status = 2,
									sub_status = 5,
									stopped_date = NOW()
									WHERE id = '".$o_main->db->escape_str($case['id'])."'";
									$o_query = $o_main->db->query($s_sql);
									
									if($needSyncing){
										$time_log['syncing_started']=microtime();
										$fromProcessCases = true;
										$creditorId = $creditor['id'];
										include(__DIR__."/import_scripts/import_cases2.php");
										$time_log['syncing_ended']=microtime();
									}
								}
								if($customer['extraName'] == ""){
									$customer_type_collect = $customer['customer_type_collect'];
									if($customer['customer_type_collect_addition'] >  0){
										$customer_type_collect = $customer['customer_type_collect_addition'] - 1;
									}
									$s_sql = "UPDATE customer SET updated = now(), extraName = ?, extraPublicRegisterId = ?, extraStreet = ?, extraPostalNumber = ?,
									extraCity = ?, extraCountry = ?, customer_type_for_collecting_cases = ?, extra_invoice_email = ?, extra_phone = ? WHERE customer.id = ?";
									$o_query = $o_main->db->query($s_sql, array($customer['name'], $customer['publicRegisterId'], $customer['paStreet'], $customer['paPostalNumber'],
									$customer['paCity'], $customer['paCountry'], $customer_type_collect+1, $customer['invoiceEmail'], $customer['phone'], $customer['id']));
								}

								$restAmount = $transaction['amount'];

								$fee_transactions = array();
								$transaction_payments = array();
								if($transaction['link_id'] > 0){
									$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
									$o_query = $o_main->db->query($s_sql, array($transaction['link_id'], $transaction['creditor_id']));
									$transaction_payments = ($o_query ? $o_query->result_array() : array());
								}
								if(count($connected_transactions) == 0) {
									foreach($transaction_payments as $transaction_payment){
										$restAmount += $transaction_payment['amount'];
									}
								}
								$invoiceDate = "0000-00-00";
								if($transaction){
									$invoiceDate = $transaction['date'];
								}
								$dueDate = $transaction['due_date'];
								// if($case){
								// 	$dueDate = $case['due_date'];
								// }

								$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
								id=NULL,
								moduleID = ?,
								created = now(),
								createdBy= ?,
								collecting_company_case_id = ?,
								name= ?,
								date = ?,
					            original_due_date=?,
					            claim_type ='1',
								amount = ?,
								original_amount = ?,
								invoice_nr = ?";
								$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $col_company_case_id, $formText_InvoiceNumber_output." ".$transaction['invoice_nr'], $invoiceDate, $dueDate, $restAmount, $transaction['amount'], $transaction['invoice_nr']));


								if(count($connected_transactions) > 0) {
									foreach($connected_transactions as $connected_transaction) {
										$invoiceDate = $connected_transaction['date'];
										$dueDate = $connected_transaction['due_date'];
										// if($case){
										// 	$dueDate = $case['due_date'];
										// }

										$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
										id=NULL,
										moduleID = ?,
										created = now(),
										createdBy= ?,
										collecting_company_case_id = ?,
										name= ?,
										date = ?,
							            original_due_date=?,
							            claim_type ='1',
										amount = ?,
										original_amount = ?,
										invoice_nr = ?";
										$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $col_company_case_id, $formText_InvoiceNumber_output." ".$connected_transaction['invoice_nr'], $invoiceDate, $dueDate, $connected_transaction['amount'], $connected_transaction['amount'], $connected_transaction['invoice_nr']));

										$s_sql = "UPDATE creditor_transactions SET
										updated = now(),
										updatedBy= ?,
										collecting_company_case_id= ?
										WHERE id = ?";
										$o_main->db->query($s_sql, array($username, $col_company_case_id, $connected_transaction['id']));
									}
									foreach($transaction_payments as $transaction_payment){
										$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
										id=NULL,
										moduleID = ?,
										created = now(),
										createdBy= ?,
										collecting_company_case_id = ?,
										name= ?,
										date = ?,
							            claim_type ='15',
										amount = ?,
										invoice_nr = ?";
										$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $col_company_case_id, $formText_Payment_output." ".$transaction_payment['invoice_nr'], $transaction_payment['date'],  $transaction_payment['amount'], $transaction_payment['invoice_nr']));
									}
								}

						        $v_return['status'] = 1;
								$v_return['collecting_company_case_id'] = $col_company_case_id;
							} else {
								$v_return['error'][] = 'Error creating case';
							}
						} else {
							$v_return['error'][] = 'Error closing fees';
						}
					} else {
						$v_return['not_signed'] = 1;
					}
				} else {
					$v_return['error'][] = 'Can not move with low amount '.$restAmount;
				}
			} else {
			}
		} else {
			$v_return['error'][] = 'Process not found';
		}
		$v_return['time_log'] = $time_log;
		$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'Move to collecting: result: '.json_encode($v_return), $creditor_syncing_id));
						
		return $v_return;
	}
}
?>
