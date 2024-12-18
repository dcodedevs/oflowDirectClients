<?php 
$cid = isset($_POST['cid']) ? $_POST['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){
	if($_POST['output_form_submit']){
        if(count($_POST['transaction_ids']) > 0) {
            if($creditor['sync_status'] != 1){
                $s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW()";
                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                if($o_query){
                    $creditor_syncing_id = $o_main->db->insert_id();
                }             

                $s_sql = "UPDATE creditor SET sync_status = 1 WHERE id = ?";
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
                
                $noFeeError3count = 0;
                $validTransactionCount = 0;
                foreach($_POST['transaction_ids'] as $transaction_id){
                    $s_sql = "SELECT * FROM creditor_transactions WHERE (system_type='InvoiceCustomer' OR  system_type='CreditnoteCustomer')
                    AND id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND open = 1 ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($transaction_id, $creditor['id']));
                    $fee_transaction = ($o_query ? $o_query->row_array() : array());

                    if($fee_transaction){
                        $validFee = false;
                        $accountNo = "";
                        $commentText = "";
                        if(strpos($fee_transaction['comment'], "_") !== false) {                            
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
                            $accountNo = $commentArray[1];
                            $commentText = $commentArray[0];
                            $validFee = true;
                        } else if(strpos($fee_transaction['comment'], "-") !== false) {
                            $s_sql = "SELECT * FROM creditor_transactions WHERE (system_type='InvoiceCustomer' OR  system_type='CreditnoteCustomer')
                            AND transaction_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND open = 0 ORDER BY created DESC";
                            $o_query = $o_main->db->query($s_sql, array($fee_transaction['comment'], $creditor['id']));
                            $parent_fee = ($o_query ? $o_query->row_array() : array());

                            if($parent_fee) {
                                if(strpos($parent_fee['comment'], "_") !== false) {   
                                    $commentArray = explode("_",$parent_fee['comment']);

                                    $accountNo = $commentArray[1];
                                    $commentText = $commentArray[0];
                                    $validFee = true;
                                }
                            }
                        }
                        if($validFee && $accountNo !="" && $commentText!= ""){
                            $validTransactionCount++;
                            $currencyName = "";
                            $invoiceDifferentCurrency = false;
                            if($fee_transaction['currency'] == 'LOCAL') {
                                $currencyName = trim($creditor['default_currency']);
                            } else {
                                $currencyName = trim($fee_transaction['currency']);
                                $invoiceDifferentCurrency = true;
                            }
            
                            $currency_rate = 1;
                            if($currencyName != "NOK") {
                                $currency_rate = $fee_transaction['currency_rate'];
                                if($currency_rate == 1){
                                    $error_with_currency = true;
                
                                    $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_currency_rates.php';
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
                            }


                            $dueDate = $fee_transaction['due_date'];
                            $hook_params = array(
                                'transaction_id' => $fee_transaction['id'],
                                'amount'=>$fee_transaction['amount']*(-1),
                                'dueDate'=>$dueDate,
                                'text'=>$commentText,
                                'type'=>$transactionType,
                                'accountNo'=>$accountNo,
                                'close'=> 1,
                                'username'=> $variables->loggID
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
                                    $o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee started: '.$transactionType, $creditor_syncing_id));

                                    $hook_result = $run_hook($hook_params);
                                    if($hook_result['result']) {
                                        $noFeeError3count++;
                                        $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee finished '.$transactionType, $creditor_syncing_id));
                                    } else {
                                        // var_dump("deleteError".$hook_result['error']);
                                        $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee failed: '.json_encode($hook_result['error']), $creditor_syncing_id));
                                    }
                                }
                                unset($run_hook);
                            }
                        }
                    }
                }
                if($noFeeError3count == $validTransactionCount) {
                    $noFeeError3 = true;
                }
                if($noFeeError3) {
                    $languageID = $_POST['languageID'];
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

                        $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($creditor['id']));

                        $v_return['status'] = 1;
                    } else {
                        $v_return['error'] = 'Missing sync script. Contact system developer';
                    }
                } else {
                    $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $v_return['error'] = 'Error with syncing. Contact system developer';
                }
            } else {
                $v_return['error'] = 'Sync already running. If sync wasn\'t finished please contact system developer';
            }
			
        } else {
            $fw_error_msg=$formText_SelectFeesToReset_output;
        }
	}
} else {
    $fw_error_msg = $formText_MissingCreditor_output;
}
?>