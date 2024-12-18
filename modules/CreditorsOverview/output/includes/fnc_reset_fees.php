<?php
if(!function_exists("reset_fees")){
    function reset_fees(){
        global $o_main;
        $triggerSync = false;
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
            if (file_exists($hook_file)) {
                include $hook_file;
                if (is_callable($run_hook)) {
                    $hook_result = $run_hook($hook_params);
                    if($hook_result['result']) {
                        $noFeeError3count++;
                        $triggerSync = true;
                        
                        $s_sql = "UPDATE creditor_transactions SET fee_marked_as_reset = 1 WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($fee_transaction['id']));
                    } else {
                        if($hook_result['error'] == -100) {
                            $v_return['error'] = 'Fiscal year is not set';
                        }
                        $s_sql = "INSERT INTO creditor_syncing_errors SET created = NOW(), creditor_id = ?, data=?, result = ?";
                        $o_query = $o_main->db->query($s_sql, array($creditor['id'], json_encode($hook_params), json_encode($hook_result['error'])));
                    }
                }
                unset($run_hook);
            }
        }


    }
}

?>