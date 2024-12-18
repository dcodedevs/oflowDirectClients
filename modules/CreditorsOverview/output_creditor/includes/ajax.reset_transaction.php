<?php 
$transaction_id = $_POST['transaction_id'];

$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($transaction_id));
$invoice = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM creditor WHERE content_status < 2 AND id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($invoice['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());

$reminder_bookaccount = 8070;
$interest_bookaccount = 8050;
if($creditor['reminder_bookaccount'] != ""){
    $reminder_bookaccount = $creditor['reminder_bookaccount'];
}
if($creditor['interest_bookaccount'] != ""){
    $interest_bookaccount = $creditor['interest_bookaccount'];
}
$currencyName = "";
$invoiceDifferentCurrency = false;
if($invoice['currency'] != ""){
    if($invoice['currency'] == 'LOCAL') {
        $currencyName = trim($creditor['default_currency']);
    } else {
        $currencyName = trim($invoice['currency']);
        $invoiceDifferentCurrency = true;
    }

}
$error_with_currency = false;
$currency_rate = 1;
if($currencyName != "NOK") {
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

$type_no = "";
$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_type_no.php';
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
if($type_no != ""){
    if(!$error_with_currency){
        $dueDate = $invoice['due_date'];

        $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND creditor_id = ? AND transaction_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($creditor['id'], $invoice['comment']));
        $parent_invoice = ($o_query ? $o_query->row_array() : array());
        if($parent_invoice){
            $commentArray = explode("_",$parent_invoice['comment']);
            if($commentArray[2] == "interest"){
                $transactionType = "interest";
            } else if($commentArray[2] == "reminderFee"){
                $transactionType = "reminderFee";
            } else if($commentArray[0] == "Rente"){
                $transactionType = "interest";
            } else {
                $transactionType = "reminderFee";
            }
            $bookaccount = $reminder_bookaccount;
            if($transactionType == "interest") {
                $bookaccount = $interest_bookaccount;
            }
            $hook_params = array(
                'transaction_id' => $invoice['id'],
                'amount'=>round($invoice['amount']/$currency_rate, 2)*-1,
                'text'=>$invoice['text']." ".date("d.m.Y"),
                'dueDate'=>$dueDate,
                'type'=>$transactionType,
                'type_no'=>$type_no,
                'accountNo'=>$bookaccount,
                'username'=> $variables->loggID,
                'close'=>1
            );
            if($invoiceDifferentCurrency) {
                $hook_params['currency'] = $currencyName;
                $hook_params['currency_rate'] = $currency_rate;
                $hook_params['currency_unit'] = 1;
            }
            $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
            $v_return['log'] = $hook_file;
            $noFeeError3 = false;
            if (file_exists($hook_file)) {
                include $hook_file;
                if (is_callable($run_hook)) {
                    $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee started: '.$transactionType.json_encode($hook_params), $creditor_syncing_id));

                    $hook_result = $run_hook($hook_params);
                    if($hook_result['result']){
                        $noFeeError3 = true;
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
            if($noFeeError3){           
                $creditorId = $creditor['id'];     
                $fromProcessCases = true;
                require(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
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
                        echo 'success';
                    } else {
                        echo 'issues';
                    }
                } else {
                    echo $failedMsg." 1";
                }
            } else {
                echo json_encode($hook_result)." 2";
            }
        } else {
            echo 'no parent invoice';
        }
    } else {
        echo 'currency error';
    }
} else {
    echo 'type no error';
}

?>