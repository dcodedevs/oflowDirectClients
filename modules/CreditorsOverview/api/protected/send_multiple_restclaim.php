<?php 

$creditor_filter = $v_data['params']['creditor_filter'];
$selected_main_transactions = $v_data['params']['selected_main_transactions'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['accounturl'];

$page = $v_data['params']['page'];
require_once __DIR__ . '/../languagesOutput/no.php';
include(__DIR__."/../../../CollectingCases/output/includes/fnc_calculate_interest.php");
include(__DIR__."/../../../CollectingCases/output/includes/fnc_generate_pdf.php");

if($creditor_filter > 0) {
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$creditorData = ($o_query ? $o_query->row_array() : array());
    if($creditorData){                     
        $reminder_bookaccount = 8070;
        $interest_bookaccount = 8050;
        if($creditorData['reminder_bookaccount'] != ""){
            $reminder_bookaccount = $creditorData['reminder_bookaccount'];
        }
        if($creditorData['interest_bookaccount'] != ""){
            $interest_bookaccount = $creditorData['interest_bookaccount'];
        }
        $currencyRates = array();                                    
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
        $type_no = "";
        $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_type_no.php';
        if (file_exists($hook_file)) {
            include $hook_file;
            if (is_callable($run_hook)) {
                $hook_params = array('creditor_id'=>$creditorData['id']);
                $hook_result = $run_hook($hook_params);
                if($hook_result['result']) {
                    $type_no = $hook_result['result'];
                }
            }
        }     
        if($type_no == "") {
            $v_return['error'] = ($fromMultiCreditorProcessing?$creditorData['companyname']." ":'').$formText_FailedToRetreiveCustomerInvoiceTypeNo_output." ".$formText_PleaseContactSupport_output."<br/>";
        } else {
            $generatePdfs = array();
            if(count($selected_main_transactions) > 0) { 
                foreach($selected_main_transactions as $selected_main_transaction) {
                    $s_sql = "SELECT * FROM creditor_transactions WHERE id = ? AND creditor_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($selected_main_transaction, $creditorData['id']));
                    $invoice = ($o_query ? $o_query->row_array() : array());
                    if($invoice){
                        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($invoice['collectingcase_id']));
                        $caseData = ($o_query ? $o_query->row_array() : array());
                        if($caseData){      
                            $noFeeError3 = true;
                            $s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%')";
                            $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
                            $fee_transactions = $o_query ? $o_query->result_array() : array();
                            if(count($fee_transactions) > 0) {
                                $noFeeError3 = false;
                            }
                            $noFeeError3count = 0;
                            $calleble_count = 0;
                            foreach($fee_transactions as $fee_transaction) {
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
                                    $calleble_count++;
                                    $hook_params = array (
                                        'transaction_id' => $fee_transaction['id'],
                                        'amount'=>$fee_transaction['amount']*(-1),
                                        'dueDate'=>$caseData['due_date'],
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
                                            $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest started rest claim', $creditor_syncing_id));

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
                            $newInterestAdded = true;
                            if($calleble_count > 0){
                                //new interest generation
                                $without_fee = 0;
                                $s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_case_id = ? ";
                                $o_query = $o_main->db->query($s_sql, array($caseData['id']));

                                $currentClaimInterest = 0;
                                $interestArray = calculate_interest($invoice, $caseData);
                                $totalInterest = 0;
                                foreach($interestArray as $interest) {
                                    $interestRate = $interest['rate'];
                                    $interestAmount = $interest['amount'];
                                    $interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
                                    $interestTo = date("Y-m-d", strtotime($interest['dateTo']));

                                    $s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
                                    date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."'";
                                    $o_query = $o_main->db->query($s_sql, array());
                                    $totalInterest += $interestAmount;
                                }
                                if($totalInterest > 0) {
                                    $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
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
                                        'dueDate'=>date("d.m.Y", strtotime("+14 days",strtotime($caseData['due_date']))),
                                        'text'=>$formText_Interest_output,
                                        'type'=>'interest',
                                        'type_no'=>$type_no,
                                        'accountNo'=>$interest_bookaccount,
                                        'username'=> $username,
                                        'caseId'=>$caseData['id'],
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
                                            $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest started rest claim', $creditor_syncing_id));

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
                            }
                            if($noFeeError3count == $calleble_count) {
                                if($newInterestAdded){                    
                                    //refresh due date of case due to new rest claim created
                                    $newDueDate = date("Y-m-d", strtotime("+14 days", time()));
                                    $s_sql = "UPDATE collecting_cases SET due_date = '".$o_main->db->escape_str($newDueDate)."' WHERE id = '".$o_main->db->escape_str($caseData['id'])."'";
                                    $o_query = $o_main->db->query($s_sql);
                                    if($o_query){
                                        $sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE collectingcase_id = ? AND creditor_id = ?";
                                        $o_query = $o_main->db->query($sql, array($caseData['id'], $creditorData['id']));
                                    }
                                    $generatePdfs[] = $caseData['id'];
                                }
                            } else {
                                $v_return['error'] = 'error syncing';
                            }
                        } else {
                            $v_return['error'] = 'missing case';
                        }
                    } else {
                        $v_return['error'] = 'missing invoice'.$o_main->db->last_query();
                    }
                }    
            }
            if(count($generatePdfs) > 0){       
                $creditorId = $creditorData['id'];
                $fromProcessCases = true;
                include(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
                foreach($generatePdfs as $generatePdf) {
                    $result = generate_pdf($generatePdf, 1);
                    if(count($result['errors']) > 0){
                        foreach($result['errors'] as $error){
                            $v_return['error'][]= $formText_LetterFailedToBeCreatedForCase_output." ".$generatePdf." ".$error."</br>";
                        }
                    } else {
                        $successfullyCreatedLetters++;
                    }
                }
            }
        }
    }
}
?>