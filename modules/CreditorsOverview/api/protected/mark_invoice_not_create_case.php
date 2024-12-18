<?php

$invoice_id = $v_data['params']['invoice_id'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$checked= $v_data['params']['checked'];
$username= $v_data['params']['username'];

if($creditor_filter > 0){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$creditor = ($o_query ? $o_query->row_array() : array());
} else {
	$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
}
if($creditor){
    $s_sql = "UPDATE creditor_invoice SET do_not_create_case = ?, updated = NOW(), updatedBy = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($checked, $username, $invoice_id));

    $v_return['status'] = 1;
}
?>
