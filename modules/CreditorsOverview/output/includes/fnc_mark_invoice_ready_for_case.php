<?php
if(!function_exists("mark_invoice_ready_for_case")){
    function mark_invoice_ready_for_case($invoiceId, $creditorId, $username = 'system'){
        global $o_main;

        $result = false;
    	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditorId));
        $creditor = ($o_query ? $o_query->row_array() : array());

        $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?";
        $o_query = $o_main->db->query($s_sql, array($invoiceId));
        $invoice = ($o_query ? $o_query->row_array() : array());
        $process_id = $creditor['reminder_process_id'];
        $process2_id = $creditor['collecting_process_id'];

        if($invoice && $creditor ) {
            $invoice_due_date = $invoice['due_date'];
            if(time() > strtotime("+ ".$creditor['days_overdue_startcase']." days", strtotime($invoice_due_date)) || $force) {
                $s_sql = "UPDATE creditor_invoice SET ready_for_create_case = 1, ready_for_create_case_date = NOW(), ready_for_create_case_person = ?, reminder_process_id = ?, collecting_process_id = ? WHERE creditor_invoice.id = ?";
                $o_query = $o_main->db->query($s_sql, array($username, $process_id, $process2_id, $invoice['id']));
                if($o_query) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}
?>
