<?php
$transaction_id = $_POST['transaction_id'];

$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_transactions.id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction_id));
$transactions_to_sync = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT *, creditor.companyname as creditorName FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($transactions_to_sync['creditor_id']));
$creditorData = ($o_query ? $o_query->row_array() : array());
if($creditorData){
	$sql = "UPDATE creditor_transactions SET updatedBy = 'import', updated=NOW(),  open = 0 WHERE id = ?";
	$o_query = $o_main->db->query($sql, array($transactions_to_sync['id']));
	if($o_query) {
		$importedSuccessfully = true;
	}
}

?>
