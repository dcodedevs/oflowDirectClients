<?php
$sql = "SELECT * FROM creditor_invoice ci
WHERE ci.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND (ci.collecting_case_id = 0 or ci.collecting_case_id is null) AND (ci.closed is null OR ci.closed = '')
AND ci.id = '".$o_main->db->escape_str($invoiceId)."'";
$o_query = $o_main->db->query($sql);
$invoices = $o_query ? $o_query->result_array() : array();

$sql = "SELECT * FROM collecting_cases_process_steps WHERE id = '".$o_main->db->escape_str($step['id'])."'";
$o_query = $o_main->db->query($sql);
$processing_step = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM collecting_cases_process WHERE id = '".$o_main->db->escape_str($processing_step['collecting_cases_process_id'])."'";
$o_query = $o_main->db->query($sql);
$process = $o_query ? $o_query->row_array() : array();

foreach($invoices as $invoice) {
    $daysPassed = false;
    $dueDateTime = strtotime($invoice['due_date']);

    $collectinglevel = $step['id'];
    $status = 0;

    $sql = "INSERT INTO collecting_cases SET creditor_id = ?, debitor_id = ?, status = ?, collectinglevel = ?, reminder_process_id = ?, createdBy = 'process', created=NOW(), last_change_date_for_process = NOW()";
    $o_query = $o_main->db->query($sql, array($invoice['creditor_id'], $invoice['debitor_id'],  $status, $collectinglevel, $process['id']));
    if($o_query) {
        $collecting_case_id = $o_main->db->insert_id();

        $claimAmount = $invoice['amount'];

        $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
        WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 0 OR before_or_after_case is null)";
        $o_query = $o_main->db->query($sql, array($invoice['invoice_number'], $invoice['creditor_id']));
        $paymentsBefore = $o_query ? $o_query->result_array() : array();

        foreach($paymentsBefore as $paymentBefore) {
            $claimAmount -= $paymentBefore['amount'];
        }

        $sql = "UPDATE creditor_invoice SET collecting_case_id = ?, collecting_case_original_claim = ? WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($collecting_case_id, $claimAmount, $invoice['id']));


		$s_sql = "INSERT INTO collecting_cases_handling
		SET id=NULL,
			createdBy='process',
			created=NOW(),
			text='Start processing',
			from_status='',
			to_status='".$o_main->db->escape_str($step['id'])."',
			`type`=0,
			collecting_case_id='".$o_main->db->escape_str($collecting_case_id)."'";
		$o_query = $o_main->db->query($s_sql);
		if($o_query)
		{
			$handling_id = $o_main->db->insert_id();

			$s_sql = "INSERT INTO collecting_cases_handling_action SET id=NULL, createdBy='process', created=NOW(), handling_id='".$o_main->db->escape_str($handling_id)."', action_type='1'";
			$o_query = $o_main->db->query($s_sql);
		}
        $sql = "SELECT * FROM collecting_cases WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($collecting_case_id));
        $case = $o_query ? $o_query->row_array() : array();
        include(__DIR__."/claim_lines_processing.php");
    }
}
?>
