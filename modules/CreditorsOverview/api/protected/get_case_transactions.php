<?php

$_POST = $v_data['params']['post'];
$username= $v_data['params']['username'];
$languageID = $_POST['languageID'];

$s_sql = "SELECT ct.* FROM creditor_transactions ct WHERE ct.collectingcase_id = ? ";
$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
$v_row = ($o_query ? $o_query->row_array() : array());
$v_return['initial_transaction'] = $v_row;
if($v_row){
	include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");
	if(is_file(__DIR__."/../../output/includes/import_scripts/import_cases2.php")){
		ob_start();
		include(__DIR__."/../../output/languagesOutput/default.php");
		if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
			include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
		}
		$creditorId = $v_row['creditor_id'];
		include(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
		// include(__DIR__."/../../output/includes/create_cases.php");
		$result_output = ob_get_contents();
		$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
		ob_end_clean();
	}
	$s_sql = "SELECT cc.* FROM collecting_cases cc WHERE cc.id = ? ";
	$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
	$caseData = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
	$creditor = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT c.* FROM customer c WHERE c.id = ? ";
	$o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
	$debitorCustomer = ($o_query ? $o_query->row_array() : array());
	$connected_transactions = array();
	$all_connected_transaction_ids = array($v_row['internalTransactionId']);
	if($v_row['link_id'] > 0 && ($creditor['checkbox_1'])) {
		$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
		$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['internalTransactionId']));
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

    $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ? AND id NOT IN(".implode(",", $all_connected_transaction_ids).")";
    $o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
    $transaction_payments = ($o_query ? $o_query->result_array() : array());

    $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
    $transaction_fees = ($o_query ? $o_query->result_array() : array());
    // foreach($transaction_fees as $transaction_fee){
    //     if(!$transaction_fee['open']){
    //         $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
    //         $o_query = $o_main->db->query($s_sql, array($transaction_fee['link_id'], $transaction_fee['creditor_id']));
    //         $fee_payments = ($o_query ? $o_query->result_array() : array());
    //         $transaction_payments = array_merge($transaction_payments, $fee_payments);
    //     }
    // }

	$processed_profiles = array();

	$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE creditor_id = ? AND content_status < 2";
	$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
	$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());
	foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
		$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
	    $o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
	    $currentProcess = $o_query ? $o_query->row_array() : array();
		$isPersonType = 0;
		if($currentProcess['available_for'] == 1){
			$isPersonType = 1;
		}

		$showProfile = false;
		$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
		if($debitorCustomer['customer_type_collect_addition'] > 0){
			$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
		}
		if($customer_type_collect_debitor == $isPersonType){
			$showProfile = true;
		}

		if($creditor_reminder_custom_profile['name'] == ""){
			$creditor_reminder_custom_profile['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
		}
		$creditor_reminder_custom_profile['isPersonType'] = $isPersonType;
		if($showProfile){
			$processed_profiles[] = $creditor_reminder_custom_profile;
		}
	}
    $v_return['connected_transactions'] = $connected_transactions;
    $v_return['debitorCustomer'] = $debitorCustomer;
    $v_return['transaction_payments'] = $transaction_payments;
    $v_return['transaction_fees'] = $transaction_fees;
    $v_return['case_profiles'] = $processed_profiles;
    $v_return['caseData'] = $caseData;

    if($_POST['output_form_submit']) {
		$initialAmount = $v_row['amount'];
		foreach($connected_transactions as $connected_transaction){
			$initialAmount += $connected_transaction['amount'];
		}
	    $amount = $initialAmount;
	    $openFeeAmount = 0;
		$mainAmountLeft = $initialAmount;
	    foreach($transaction_fees as $transaction_fee) {
	        $amount += $transaction_fee['amount'];
	        if($transaction_fee['open']) {
	            $openFeeAmount += $transaction_fee['amount'];
	        }
	    }
	    foreach($transaction_payments as $transaction_payment) {
	        $amount += $transaction_payment['amount'];
			$mainAmountLeft+=$transaction_payment['amount'];
	    }

		// if(bccomp($openFeeAmount, $amount) == 0) {
	        if($creditor) {
	            if($creditor['sync_status'] != 1) {
					$notFullData = false;
					if($_POST['reset_case_fully']){
						$notFullData = true;
						if($_POST['profile_id'] > 0) {
							$s_sql = "SELECT creditor_reminder_custom_profiles.id FROM creditor_reminder_custom_profiles
							WHERE creditor_reminder_custom_profiles.id = '".$o_main->db->escape_str($_POST['profile_id'])."' AND creditor_reminder_custom_profiles.creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
							$o_query = $o_main->db->query($s_sql);
							$profile_exists = ($o_query ? $o_query->row_array() : array());
							if($profile_exists){
								$notFullData = false;
							}
						}
					}
					if(!$notFullData){
						$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW()";
						$o_query = $o_main->db->query($s_sql, array($creditor['id']));
						if($o_query){
							$creditor_syncing_id = $o_main->db->insert_id();
						}

						$currencyName = "";
						$invoiceDifferentCurrency = false;
						if($v_row['currency'] != "") {
							if($v_row['currency'] == 'LOCAL') {
								$currencyName = trim($creditor['default_currency']);
							} else {
								$currencyName = trim($v_row['currency']);
								$invoiceDifferentCurrency = true;
							}
							if($currencyName != ""){
								$currency_rate = 1;
								if($currencyName != "NOK") {
									$currency_rate = $v_row['currency_rate'];
								// 	$error_with_currency = true;

								// 	$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_currency_rates.php';
								// 	if (file_exists($hook_file)) {
								// 	   include $hook_file;
								// 	   if (is_callable($run_hook)) {
								// 			$hook_result = $run_hook(array("creditor_id"=>$creditor['id']));
								// 			if(count($hook_result['currencyRates']) > 0){
								// 				$currencyRates = $hook_result['currencyRates'];
								// 				foreach($currencyRates as $currencyRate) {
								// 					if($currencyRate['symbol'] == $currencyName) {
								// 						$currency_rate = $currencyRate['rate'];
								// 						$error_with_currency = false;
								// 						break;
								// 					}
								// 				}
								// 			}
								// 	   }
								//    }
								}
								if(!$error_with_currency){
					                $s_sql = "UPDATE creditor SET sync_status = 1, sync_started_time = now() WHERE id = ?";
					                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
					                $reminder_bookaccount = 8070;
					                $interest_bookaccount = 8050;
					                if($creditor['reminder_bookaccount'] != ""){
					                    $reminder_bookaccount = $creditor['reminder_bookaccount'];
					                }
					                if($creditor['interest_bookaccount'] != ""){
					                    $interest_bookaccount = $creditor['interest_bookaccount'];
					                }

					                $open_fees = array();
					                foreach($transaction_fees as $transaction_fee){
					                    if($transaction_fee['open']){
					                        $open_fees[] = $transaction_fee;
					                    }
					                }
					                $noFeeError3 = true;
					                if(count($open_fees) > 0){
					                    $noFeeError3 = false;
					                }
					                $noFeeError3count = 0;


					                foreach($open_fees as $fee_transaction){
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
					                    $dueDate = $fee_transaction['due_date'];
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
										if($invoiceDifferentCurrency) {
											$hook_params['currency'] = $currencyName;
											$hook_params['currency_rate'] = $currency_rate;
											$hook_params['currency_unit'] = 1;
										}

					                    $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
					                    $v_return['log'] = $hook_file;
					                    if (file_exists($hook_file)) {
					                        include $hook_file;
					                        if (is_callable($run_hook)) {
												$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
												$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee started: '.$fee_transaction['id'], $creditor_syncing_id));

					                            $hook_result = $run_hook($hook_params);
					                            if($hook_result['result']) {
					                                $noFeeError3count++;
													$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
													$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee finished '.$fee_transaction['id'], $creditor_syncing_id));
					                            } else {
													if($hook_result['error'] == -100) {
														$v_return['error'] = 'Fiscal year is not set';
													}
					                                // var_dump("deleteError".$hook_result['error']);
													$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
													$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee failed: '.$fee_transaction['id']." ".json_encode($hook_result['error']), $creditor_syncing_id));
					                            }
					                        }
					                        unset($run_hook);
					                    }
					                }
					                if($noFeeError3count == count($open_fees)){
					                    $noFeeError3 = true;
					                }
					                if($noFeeError3){
										$transaction_ids = array($v_row['transaction_id']);
									    foreach($transaction_payments as $transaction_payment) {
											$transaction_ids[]=$transaction_payment['transaction_id'];
										}
										$hook_params = array(
											'transaction_ids' => $transaction_ids,
											'creditor_id'=>$creditor['id'],
											'username'=>$username
										);
										if(count($transaction_ids) > 1){
											$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/relink_transaction.php';
											if (file_exists($hook_file)) {
												include $hook_file;
												if (is_callable($run_hook)) {
													$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
													$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link started: '.$transactionType, $creditor_syncing_id));

													$hook_result = $run_hook($hook_params);
													if($hook_result['result']){
														$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
														$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link finished '.$transactionType, $creditor_syncing_id));
													} else {
														// var_dump("deleteError".$hook_result['error']);
														$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
														$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link failed: '.json_encode($hook_result), $creditor_syncing_id));
													}
												}
												unset($run_hook);
											}
										}


					                    if(is_file(__DIR__."/../../output/includes/import_scripts/import_cases2.php")){
					                        ob_start();
					                        include(__DIR__."/../../output/languagesOutput/default.php");
					                        if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
					                            include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
					                        }
					                        $creditorId = $creditor['id'];
											$fromResetFees = true;
					                        include(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
					                        // include(__DIR__."/../../output/includes/create_cases.php");
					                        $result_output = ob_get_contents();
					                        $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
					                        ob_end_clean();
											if($_POST['reset_case_fully']) {
												$transactionDueDate = $v_row['due_date'];
												$s_sql = "UPDATE collecting_cases SET updated=NOW(), reminder_profile_id = '".$o_main->db->escape_str($_POST['profile_id'])."', due_date = '".$o_main->db->escape_str($transactionDueDate)."', collecting_cases_process_step_id = 0  WHERE id = ?";
												$o_query = $o_main->db->query($s_sql, array($caseData['id']));

												$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
												WHERE id = '".$o_main->db->escape_str($v_row['id'])."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
												$o_query = $o_main->db->query($s_sql);
												//trigger reordering 
												$source_id = 4;
												process_open_cases_for_tabs($creditor['id'], $source_id);

						                        $v_return['status'] = 1;
											} else {
						                        $v_return['status'] = 1;
											}
					                    } else {
					                        $v_return['error'] = 'Missing sync script. Contact system developer';
					                    }

										$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($creditor['id']));
					                } else {
					                    $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
					                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
										if($v_return['error'] == ''){
						                    $v_return['error'] = 'Error with syncing. Please try again later';
										}
					                }
								} else {
									$v_return['error'] = 'Error with currency retrieval. Please try again later';
								}
							}
						} else {
							$v_return['error'] = 'Transaction missing currency info. Contact system developer';
						}
					} else {
						$v_return['error'] = 'Wrong profile';
					}
	            } else {
	                $v_return['error'] = 'Sync already running. If sync wasn\'t finished please contact system developer';
	            }
			}
        // } else {
        //     $v_return['error'] = 'Missing customer. Contact system developer';
        // }
    } else {
        $v_return['status'] = 1;
    }
}

?>
