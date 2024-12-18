<?php
if(!function_exists("create_case")){
    function create_case($invoiceId, $creditorId, $process_id, $username = 'system', $launch_process = false, $override_action = 0, $skip_to_step = 0) {
        global $o_main;

        $result = false;
    	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditorId));
        $creditor = ($o_query ? $o_query->row_array() : array());

        $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?";
        $o_query = $o_main->db->query($s_sql, array($invoiceId));
        $ready_invoice = ($o_query ? $o_query->row_array() : array());

        $process_id = $creditor['reminder_process_id'];
        $collecting_process_id = $creditor['collecting_process_id'];

        if($ready_invoice && $creditor) {
            $s_sql = "UPDATE creditor_invoice SET ready_for_create_case = 1, ready_for_create_case_date = NOW(), ready_for_create_case_person = ?, reminder_process_id = ?, collecting_process_id = ? WHERE creditor_invoice.id = ?";
            $o_query = $o_main->db->query($s_sql, array($username, $process_id, $collecting_process_id, $ready_invoice['id']));
            if($o_query) {
                $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array($process_id));
                $process_for_handling_cases = ($o_query ? $o_query->row_array() : array());
                if($process_for_handling_cases) {
                    $sql = "INSERT INTO collecting_cases SET creditor_id = ?, debitor_id = ?, status = ?, collecting_cases_process_step_id = ?, reminder_process_id = ?, collecting_process_id = ?, createdBy = 'process', created=NOW(), due_date = ?";
                    $o_query = $o_main->db->query($sql, array($ready_invoice['creditor_id'], $ready_invoice['debitor_id'],  0, 0, $process_id, $collecting_process_id, $ready_invoice['due_date']));
                    if($o_query) {
                        $result = true;

                        $collecting_case_id = $o_main->db->insert_id();

                        $claimAmount = $ready_invoice['amount'];

                        $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                        WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 0 OR before_or_after_case is null)";
                        $o_query = $o_main->db->query($sql, array($ready_invoice['invoice_number'], $ready_invoice['creditor_id']));
                        $paymentsBefore = $o_query ? $o_query->result_array() : array();

                        foreach($paymentsBefore as $paymentBefore) {
                            $claimAmount += $paymentBefore['amount'];
                        }

                        $sql = "UPDATE creditor_invoice SET collecting_case_id = ?, collecting_case_original_claim = ? WHERE id = ?";
                        $o_query = $o_main->db->query($sql, array($collecting_case_id, $claimAmount, $ready_invoice['id']));

                        if($launch_process){
                            $creditorId = $creditor['id'];
                            include(__DIR__."/process_scripts/handle_cases.php");
                        }
                    }
                }
            }
        }
        return $result;
    }
}
?>
