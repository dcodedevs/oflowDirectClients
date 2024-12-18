<?php

$invoice_id = $v_data['params']['invoice_id'];
$customer_id = $v_data['params']['customer_id'];
$process_id= $v_data['params']['process_id'];
$username= $v_data['params']['username'];

$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
$o_query = $o_main->db->query($s_sql, array($customer_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
    $o_query = $o_main->db->query($s_sql, array($process_id));
    $process_for_suggested_cases = ($o_query ? $o_query->row_array() : array());
    if($process_for_suggested_cases){
        include(__DIR__."/../../output/includes/fnc_mark_invoice_ready_for_case.php");
        $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?
        AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
        AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (ready_for_create_case = 0 OR ready_for_create_case is null)";
        $o_query = $o_main->db->query($s_sql, array($invoice_id));
        $open_invoices = ($o_query ? $o_query->result_array() : array());
        foreach($open_invoices as $open_invoice){
            $invoice_due_date = $open_invoice['due_date'];
            if(time() > strtotime("+ ".$creditor['days_overdue_startcase']." days", strtotime($invoice_due_date))) {
                mark_invoice_ready_for_case($open_invoice['id'], $creditor['id'], $process_id, $username);
            }
        }

        $v_return['status'] = 1;
    } else {
        $v_return['error'] = 'Process not found';
    }
}
?>
