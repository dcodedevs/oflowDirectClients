<?php
if($_POST['transation_id'] > 0 && $_POST['cid'] > 0) {
	$s_sql = "SELECT * FROM creditor_transactions WHERE id = '".$o_main->db->escape_str($_POST['transation_id'])."'
	AND collecting_company_case_id = '".$o_main->db->escape_str($_POST['cid'])."'";
	$o_query = $o_main->db->query($s_sql);
	$connected_transaction = ($o_query ? $o_query->row_array() : array());
	if($connected_transaction) {
		$s_sql = "UPDATE creditor_transactions SET collecting_company_case_id = 0 WHERE id = '".$o_main->db->escape_str($connected_transaction['id'])."'";
		$o_query = $o_main->db->query($s_sql);
	}
}
?>
