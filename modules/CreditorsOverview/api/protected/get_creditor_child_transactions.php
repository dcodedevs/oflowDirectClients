<?php 
$creditor_id = $v_data['params']['creditor_id'];
$transaction_id = $v_data['params']['transaction_id'];


$v_return['status'] = 0;
$s_sql = "SELECT * FROM creditor_transactions 
WHERE creditor_id = ? AND id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id, $transaction_id));
$transaction = ($o_query ? $o_query->row_array() : array());
if($transaction){
    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_id));
    $creditor = ($o_query ? $o_query->row_array() : array());
    
    $transaction_fees = array();
    $transaction_payments = array();
    $connected_transactions = array();
    if($transaction['link_id'] > 0){
        $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? 
        AND (collectingcase_id is null OR collectingcase_id = 0) 
        AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') AND link_id = ?";
        $o_query = $o_main->db->query($s_sql, array($transaction['creditor_id'], $transaction['link_id']));
        $transaction_fees = ($o_query ? $o_query->result_array() : array());
        
        $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') 
        AND creditor_id = ? AND link_id = ?";
        $o_query = $o_main->db->query($s_sql, array($transaction['creditor_id'], $transaction['link_id']));
        $transaction_payments = ($o_query ? $o_query->result_array() : array());

        if($creditor['checkbox_1']){
            $s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND ct.open = 1 AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
            $o_query = $o_main->db->query($s_sql, array($transaction['link_id'], $transaction['id']));
            $connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
            foreach($connected_transactions_raw as $connected_transaction_raw){
                if(strpos($connected_transaction_raw['comment'], '_') === false){
                    $connected_transactions[] = $connected_transaction_raw;
                }
            }
        }
    }
    
	$v_return['transaction_payments'] = $transaction_payments;
	$v_return['transaction_fees'] = $transaction_fees;
	$v_return['connected_transactions'] = $connected_transactions;
	$v_return['transaction'] = $transaction;
	$v_return['status'] = 1;
}
?>