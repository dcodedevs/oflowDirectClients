<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $transaction_id = $data['transaction_id'];
    $amount = $data['amount'];
    $text = $data['text'];
    $accountNo = $data['accountNo'];
    $dueDate = $data['dueDate'];
    $date = $data['date'];
    
	if($data['date'] == ""){
		$date = date("Y-m-d");
	}
	if($date != ""){
        $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($transaction_id));
        $transaction = ($o_query ? $o_query->row_array() : array());

        $sql = "SELECT * FROM creditor WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($transaction['creditor_id']));
        $creditorData = $o_query ? $o_query->row_array() : array();
        // Return object
        $return = array();
        $return['result'] = 0;
        if($creditorData){
            require_once __DIR__ . '/../internal_api/load.php';
            $v_config = array(
                'ownercompany_id' => 1,
                'identityId' => $creditorData['entity_id'],
                'creditorId' => $creditorData['id'],
                'o_main' => $o_main
            );
            $s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($data['username'])."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
            $o_query = $o_main->db->query($s_sql);
            if($o_query && 0 < $o_query->num_rows())
            {
                $v_int_session = $o_query->row_array();
                $v_config['session_id'] = $v_int_session['session_id'];
            }
            $api = new Integration24SevenOffice($v_config);
            $insertTransaction = true;
            if($data['close']){  
                $insertTransaction = false;              
                $s_sql = "SELECT * FROM creditor_transactions WHERE comment = ? AND creditor_id = ? AND open = 1";
                $o_query = $o_main->db->query($s_sql, array($transaction['id'], $creditorData['id']));
                $reset_transaction_exist = $o_query ? $o_query->row_array() : array();
                if(!$reset_transaction_exist){
                    $insertTransaction = true;   
                }
            }
            if($insertTransaction){
                $search_data = array(
                    'transaction_guid'=>$transaction['transaction_id'],
                    'amount'=>$amount,
                    'text'=>$text,
                    'accountNo'=>$accountNo,
                    'customerId'=>$transaction['external_customer_id'],
                    'date'=>date("c", strtotime($date)),
                    'dueDate'=>date("c", strtotime($dueDate)),
                    'type' => $data['type'],
                    'close'=>$data['close'],
                    'invoice_nr'=>$transaction['invoice_nr'],
                    'kid_number'=>$transaction['kid_number'],
                    'project_id'=>$transaction['integration_project_id'],
                    'department_id'=>$transaction['integration_department_id']
                );
                if(isset($data['currency'])) {
                    $search_data['currency'] = $data['currency'];
                }
                if(isset($data['currency_rate'])) {
                    $search_data['currency_rate'] = $data['currency_rate'];
                }
                if(isset($data['currency_unit'])) {
                    $search_data['currency_unit'] = $data['currency_unit'];
                }
                // var_dump($search_data);
                $customer_info = $api->insert_transactions($search_data);
                if($customer_info['result']){
                    $return['result'] = 1;
                } else {
                    $return['error'] = $customer_info['error'];
                }
                $return['params'] = $customer_info['params'];
            } else {
                $return['error'] = 'reset transaction already exists';
            }
        }
    } else {
        $return['error'] = 'date missing';
    }
    return $return;
}

?>
