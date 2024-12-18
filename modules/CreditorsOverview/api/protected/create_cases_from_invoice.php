<?php

$invoice_id = $v_data['params']['invoice_id'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$processed_cases= $v_data['params']['processed_cases'];
$processed_override_actions= $v_data['params']['processed_override_actions'];
$case_steps= $v_data['params']['case_steps'];

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
if($creditor) {
    foreach($processed_cases as $invoice_id => $process_id) {
        $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
        $o_query = $o_main->db->query($s_sql, array($process_id));
        $process_for_suggested_cases = ($o_query ? $o_query->row_array() : array());

        if($process_for_suggested_cases){
            include(__DIR__."/../../output/includes/fnc_create_case.php");
            $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?
            AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
            AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (creditor_invoice.onhold_by_creditor is null OR creditor_invoice.onhold_by_creditor = 0)";
            $o_query = $o_main->db->query($s_sql, array($invoice_id));
            $open_invoice = ($o_query ? $o_query->row_array() : array());
            if($open_invoice){
                create_case($open_invoice['id'], $creditor['id'], $process_id, $username, true, $processed_override_actions[$invoice_id], $case_steps[$invoice_id]);
            }

            $v_return['status'] = 1;
        } else {
        }
    }
}
?>