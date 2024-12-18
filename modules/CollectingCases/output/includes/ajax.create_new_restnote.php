<?php 
$username= $variables->loggID;
if($username == "byamba@dcode.no"){
    $username = "david@dcode.no";
}
include(__DIR__."/fnc_generate_pdf.php");
$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
$case = $o_query ? $o_query->row_array() : array();
include(dirname(__FILE__).'/../languagesOutput/no.php');
if($case){
    require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
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
	$creditorData = ($o_query ? $o_query->row_array() : array());

    
    $v_config = array(
        'ownercompany_id' => 1,
        'identityId' => $creditorData['entity_id'],
        'creditorId' => $creditorData['id'],
        'o_main' => $o_main
    );
    $s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && 0 < $o_query->num_rows())
    {
        $v_int_session = $o_query->row_array();
        $v_config['session_id'] = $v_int_session['session_id'];
    }
    $invoicesTransactions = array();
    $connectedSuccessfully = false;
    try {
        $api = new Integration24SevenOffice($v_config);
        if($api->error == "") {
            $currencyRates = array();
                        
            $reminder_bookaccount = 8070;
            $interest_bookaccount = 8050;
            if($creditorData['reminder_bookaccount'] != ""){
                $reminder_bookaccount = $creditorData['reminder_bookaccount'];
            }
            if($creditorData['interest_bookaccount'] != ""){
                $interest_bookaccount = $creditorData['interest_bookaccount'];
            }					
            $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_currency_rates.php';
            if (file_exists($hook_file)) {
                include $hook_file;
                if (is_callable($run_hook)) {
                    $hook_result = $run_hook(array("creditor_id"=>$creditorData['id']));
                    if(count($hook_result['currencyRates']) > 0){
                        $currencyRates = $hook_result['currencyRates'];
                    }
                }
            }
            $doNotTriggerInitialSync = true;
            $creditorId = $creditorData['id'];
            require(__DIR__."/../../../CreditorsOverview/output/includes/import_scripts/import_cases2.php");
            $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
            $o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
            $invoice = ($o_query ? $o_query->row_array() : array());

            //delete existing interest
            $noFeeError3 = true;
            $s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) 
            AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%')";
            $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
            $fee_transactions = $o_query ? $o_query->result_array() : array();
            if(count($fee_transactions) > 0) {
                $noFeeError3 = false;
            }
            $noFeeError3count = 0;
            $calleble_count = 0;
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
                if($transactionType == "interest"){														
                    $currencyName = "";
                    $invoiceDifferentCurrency = false;
                    if($fee_transaction['currency'] == 'LOCAL') {
                        $currencyName = trim($creditorData['default_currency']);
                    } else {
                        $currencyName = trim($fee_transaction['currency']);
                        $invoiceDifferentCurrency = true;
                    }		
                    $currency_rate = 1;
                    if($currencyName != "NOK") {
                        $currency_rate = $fee_transaction['currency_rate'];
                        if($currency_rate == 1){
                            $error_with_currency = true;														
                            foreach($currencyRates as $currencyRate) {
                                if($currencyRate['symbol'] == $currencyName) {
                                    $currency_rate = $currencyRate['rate'];
                                    $error_with_currency = false;
                                    break;
                                }
                            }
                        }
                    }
                    $calleble_count++;
                    $hook_params = array (
                        'transaction_id' => $fee_transaction['id'],
                        'amount'=>$fee_transaction['amount']*(-1),
                        'dueDate'=>$dueDate,
                        'text'=>$commentArray[0],
                        'type'=>$transactionType,
                        'accountNo'=>$commentArray[1],
                        'close'=> 1
                    );
                    if($invoiceDifferentCurrency) {
                        $hook_params['currency'] = $currencyName;
                        $hook_params['currency_rate'] = $currency_rate;
                        $hook_params['currency_unit'] = 1;
                    }

                    $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
                    if (file_exists($hook_file)) {
                        include $hook_file;
                        if (is_callable($run_hook)) {
                            $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
                            $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest started', $creditor_syncing_id));

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
                                $noFeeError3count++;
                                $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
                                $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest finished', $creditor_syncing_id, $connect_tries));
                            } else {
                                // var_dump("deleteError".$hook_result['error']);
                                $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
                                $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
                            }
                        }
                    }
                }
            }
            if($calleble_count > 0 && $noFeeError3count == $calleble_count){
                //new interest generation
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
                    $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($case['collecting_cases_process_step_id']));
                    $step = ($o_query ? $o_query->row_array() : array());
                    
                    $currencyName = "";
                    $invoiceDifferentCurrency = false;
                    if($invoice['currency'] == 'LOCAL') {
                        $currencyName = trim($creditorData['default_currency']);
                    } else {
                        $currencyName = trim($invoice['currency']);
                        $invoiceDifferentCurrency = true;
                    }		
                    $currency_rate = 1;
                    if($currencyName != "NOK") {
                        $error_with_currency = true;														
                        foreach($currencyRates as $currencyRate) {
                            if($currencyRate['symbol'] == $currencyName) {
                                $currency_rate = $currencyRate['rate'];
                                $error_with_currency = false;
                                break;
                            }
                        }
                    }
                    $hook_params = array (
                        'transaction_id' => $invoice['id'],
                        'amount'=>$totalInterest,
                        'dueDate'=>$case['due_date'],
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
                    $newInterestAdded = false;
                    $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
                    if (file_exists($hook_file)) {
                        include $hook_file;
                        if (is_callable($run_hook)) {
                            $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
                            $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest started', $creditor_syncing_id));

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
                                $newInterestAdded = true;
                                $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
                                $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest finished', $creditor_syncing_id, $connect_tries));
                            } else {
                                // var_dump("deleteError".$hook_result['error']);
                                $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
                                $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
                            }
                        }
                    }
                }
                if($newInterestAdded){
                    $triggerSync = true;
                }
                if($triggerSync){
                    $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 started - syncing after links created', $creditor_syncing_id));
                    $connect_tries = 0;
                    $connectedSuccessfully = false;
                    $transactionData = array();

                    $changedAfterDate = isset($creditorData['lastImportedDateTimestamp']) ? $creditorData['lastImportedDateTimestamp'] : "";
                    if($changedAfterDate != null && $changedAfterDate != ""){
                        $changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
                        $now = DateTime::createFromFormat('U.u', $changedAfterDate);
                        if($now){
                            $transactionData['date_start'] = $now->format("Y-m-d\TH:i:s.u");
                            $dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));
                        }
                    }
                    $transactionData['DateSearchParameters'] = 'DateChangedUTC';
                    $transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
                    $transactionData['bookaccountStart'] = 1500;
                    $transactionData['bookaccountEnd'] = 1529;
                    $transactionData['ShowOpenEntries'] = null;
                    do {
                        $connect_tries++;
                        $invoicesTransactions = $api->get_transactions($transactionData);
                        if($invoicesTransactions !== null){
                            $connectedSuccessfully = true;
                            break;
                        }
                    } while($connect_tries < 11);
                    $connect_tries--;
                    list($totalImportedSuccessfully_links, $lastImportedDate_links, $cases_to_check_links, $totalSum_links) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, true);
                    if($totalImportedSuccessfully_links > 0) {
                        if($lastImportedDate_links != ""){
                            $dateTime = new DateTime($lastImportedDate_links);
                            $timestamp = $dateTime->format("U");
                            $microseconds = $dateTime->format("u");
                            $sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
                            $o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));

                            $sql = "SELECT * FROM creditor_transactions
							WHERE creditor_id = ? AND open = 1 AND link_id is null AND comment is not null AND date_changed >= '".date("Y-m-d", strtotime("-10 days"))."'";
		                    $o_query = $o_main->db->query($sql, array($creditorData['id']));
		                    $local_transactions = $o_query ? $o_query->result_array() : array();
							$connectedSuccessfully = true;
							$total_transaction_to_link = 0;
							$current_transaction_success = 0;
                            $triggerSync = false;
		                    foreach($local_transactions as $local_transaction) {
								if($local_transaction['comment'] != ""){
									$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ? AND open = 1";
									$o_query = $o_main->db->query($sql, array($local_transaction['comment'], $creditorData['id']));
									$parent_transaction = $o_query ? $o_query->row_array() : array();
									if($parent_transaction){
										$connect_tries = 0;
										$total_transaction_to_link++;
										do {
											$connect_tries++;
											$linkArray = array();
											$linkArray['transaction1_id'] = $parent_transaction['transaction_id'];
											$linkArray['transaction2_id'] = $local_transaction['transaction_id'];
											$links_created_result = $api->create_link($linkArray);
											if($links_created_result){
												$current_transaction_success++;
												$triggerSync = true;
												break;
											}
										} while($connect_tries < 11);
										$connect_tries--;
										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?";
										$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'Transaction links result '.json_encode($links_created_result).', data: '.json_encode($linkArray), $creditor_syncing_id, $connect_tries));
									}
								}
		                    }
                            if($triggerSync){
                                $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
                                $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 started - syncing after links created', $creditor_syncing_id));
                                $connect_tries = 0;
                                $connectedSuccessfully = false;
                                $transactionData = array();

                                $changedAfterDate = isset($creditorData['lastImportedDateTimestamp']) ? $creditorData['lastImportedDateTimestamp'] : "";
                                if($changedAfterDate != null && $changedAfterDate != ""){
                                    $changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
                                    $now = DateTime::createFromFormat('U.u', $changedAfterDate);
                                    if($now){
                                        $transactionData['date_start'] = $now->format("Y-m-d\TH:i:s.u");
                                        $dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));
                                    }
                                }
                                $transactionData['DateSearchParameters'] = 'DateChangedUTC';
                                $transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
                                $transactionData['bookaccountStart'] = 1500;
                                $transactionData['bookaccountEnd'] = 1529;
                                $transactionData['ShowOpenEntries'] = null;
                                do {
                                    $connect_tries++;
                                    $invoicesTransactions = $api->get_transactions($transactionData);
                                    if($invoicesTransactions !== null){
                                        $connectedSuccessfully = true;
                                        break;
                                    }
                                } while($connect_tries < 11);
                                $connect_tries--;
                                list($totalImportedSuccessfully_links, $lastImportedDate_links, $cases_to_check_links, $totalSum_links) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, true);
                                if($totalImportedSuccessfully_links > 0) {
                                    if($lastImportedDate_links != ""){
                                        $dateTime = new DateTime($lastImportedDate_links);
                                        $timestamp = $dateTime->format("U");
                                        $microseconds = $dateTime->format("u");
                                        $sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
                                        $o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));

                                        $sql = "SELECT * FROM creditor_transactions
                                        WHERE creditor_id = ? AND open = 1 AND link_id is null AND comment is not null AND date_changed >= '".date("Y-m-d", strtotime("-10 days"))."'";
                                        $o_query = $o_main->db->query($sql, array($creditorData['id']));
                                        $local_transactions = $o_query ? $o_query->result_array() : array();
                                        $connectedSuccessfully = true;
                                    }
                                }
                            }
                        }
                    }
                    $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, number_of_transactions = ?";
                    $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 ended - syncing after links created', $creditor_syncing_id, $connect_tries, count($invoicesTransactions)));
                
                        
                    $toBePaid = $invoice['collecting_case_original_claim'];

                    $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
                    $invoice_payments = ($o_query ? $o_query->result_array() : array());

                    $total_transaction_payments = $invoice_payments;

                    $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
                    $claim_transactions = ($o_query ? $o_query->result_array() : array());
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
                    if($creditorData['use_customized_reminder_rest_note_min_amount']){
                        $reminderRestNoteMinimumAmount = $creditorData['reminderRestNoteMinimumAmount'];
                    }
                    $lettersForDownload = array();
                    $leftToBePaid = $toBePaid + $payments;
                    if($leftToBePaid >= $reminderRestNoteMinimumAmount) {
                        // $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id='".$o_main->db->escape_str($case['id'])."'
                        // AND step_id = '".$o_main->db->escape_str($case['collecting_cases_process_step_id'])."' AND rest_note = '1'";
                        // $o_query = $o_main->db->query($s_sql);
                        // $rest_letter = $o_query ? $o_query->row_array() : array();
                        // if(!$rest_letter) {
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
                                if($creditorData['print_reminders'] == 0) {
                                    if($result['item']['id'] > 0){
                                        $s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(),  print_batch_code = ? WHERE id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($code, $result['item']['id']));
                                        if($o_query) {
                                            $lettersForDownload[] = $result['item']['id'];

                                            if(count($lettersForDownload) > 0){
                                                echo $formText_LettersForManualPrinting_output." <a href='".$extradomaindirroot."/modules/CollectingCaseClaimletter/output/includes/ajax.download.php?code=".$code."&ids=".implode(",",$lettersForDownload)."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>"."<br/>";
                                            }
                                        }
                                    }
                                }
                            }
                        // } else {
                        //     $fw_error_msg[] = $formText_RestNoteAlreadyCreatedForThisStep_output;
                        // }
                    } else {                    
                        $fw_error_msg[] = $formText_TooLittleToCreateRestNote_output;
                    }
                } else {
                    $fw_error_msg[] = $formText_FailedToSync_output;
                }
            } else {
                $fw_error_msg[] = $formText_FailedResettingFee_output;
            }
        } else {
            echo $api->error;
        }
    } catch(Exception $e) {
        $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditorData['id']));

        $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'Error '.$e->getMessage(), $creditor_syncing_id));
        echo $formText_FailedToConnect_output."<br/>";
        $connection_error = true;
        $failedMsg = "Critical error with exception. ".$e->getMessage();
    }
} else {
    $fw_error_msg[] = $formText_CaseNotFound_output;
}
?>