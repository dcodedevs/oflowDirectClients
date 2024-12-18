<?php
$transaction_id = $_POST['transaction_id'];

$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_transactions.id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction_id));
$transaction = ($o_query ? $o_query->row_array() : array());
$s_sql = "UPDATE creditor_transactions SET open = 0 WHERE creditor_transactions.id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction['id']));
if($o_query){
	echo 'closed';
} else {
	echo 'failed to close';
}

?>
