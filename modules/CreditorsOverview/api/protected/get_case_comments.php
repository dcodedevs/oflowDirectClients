<?php
$_POST = $v_data['params']['post'];
$transaction_id = $_POST['transaction_id'];
$customer_id = $_POST['customer_id'];


$sql = "SELECT * FROM creditor_transactions WHERE id = '".$o_main->db->escape_str($transaction_id)."'";
$o_query = $o_main->db->query($sql);
$transactionData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT collecting_cases_comments.* FROM collecting_cases_comments
WHERE collecting_cases_comments.transaction_id = '".$o_main->db->escape_str($transactionData['id'])."'
ORDER BY collecting_cases_comments.created DESC";
$o_query = $o_main->db->query($s_sql);
$comments = $o_query ? $o_query->result_array() : array();

$v_return['status'] = 1;
$v_return['comments'] = $comments;
$v_return['transactionData'] = $transactionData;
?>
