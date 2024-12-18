<?php
set_time_limit(300);
$sql = "SELECT creditor_transactions.transaction_id, COUNT(creditor_transactions.id) AS duplicates FROM creditor_transactions WHERE open = 1 GROUP BY transaction_id HAVING duplicates > 1";
$o_query = $o_main->db->query($sql);
$duplicated_transactions = $o_query ? $o_query->result_array() : array();

$creditors_with_duplicate = array();
foreach($duplicated_transactions as $duplicated_transaction){
	$transaction_id = $duplicated_transaction['transaction_id'];
	$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ?";
	$o_query = $o_main->db->query($sql, array($transaction_id));
	$transactions = $o_query ? $o_query->result_array() : array();
	foreach($transactions as $transaction){
		if(!in_array($transaction['creditor_id'], $creditors_with_duplicate)){
			$creditors_with_duplicate[] = $transaction['creditor_id'];
		}
	}
}
sort($creditors_with_duplicate);
echo count($creditors_with_duplicate)."<br/>";
echo implode(",", $creditors_with_duplicate);
?>
