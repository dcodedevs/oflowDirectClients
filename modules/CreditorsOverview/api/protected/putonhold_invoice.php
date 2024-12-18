<?php

$invoice_id = $v_data['params']['invoice_id'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$process_id= $v_data['params']['process_id'];
$username= $v_data['params']['username'];
$remove= $v_data['params']['remove'];

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
    $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?
    AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
    AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (ready_for_create_case = 0 OR ready_for_create_case is null)";
    $o_query = $o_main->db->query($s_sql, array($invoice_id));
    $invoice = ($o_query ? $o_query->row_array() : array());
    if($invoice){
        if($remove){
            $s_sql = "UPDATE creditor_invoice SET onhold_by_creditor = 0 WHERE id = '".$o_main->db->escape_str($invoice['id'])."'";
            $o_query = $o_main->db->query($s_sql);
        } else {
            $s_sql = "UPDATE creditor_invoice SET onhold_by_creditor = 1 WHERE id = '".$o_main->db->escape_str($invoice['id'])."'";
            $o_query = $o_main->db->query($s_sql);
        }
        if($o_query) {
            $v_return['status'] = 1;
        } else {
            $v_return['error'] = 'Error updating database';
        }
    }
}
?>
