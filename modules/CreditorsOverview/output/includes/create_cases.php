<?php
include(__DIR__."/fnc_create_case.php");

$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql, array($creditor['reminder_process_id']));
$process_for_handling_cases = ($o_query ? $o_query->row_array() : array());
if($process_for_handling_cases){

    $s_sql = "SELECT creditor_transactions.* FROM creditor_transactions WHERE creditor_transactions.creditor_id = ?
    AND (creditor_transactions.collectingcase_id is null  OR creditor_transactions.collectingcase_id = 0)
    AND (creditor_invoice.open = 1)";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    $ready_invoices = ($o_query ? $o_query->result_array() : array());

    $newCaseCount = 0;
    foreach($ready_invoices as $ready_invoice) {
        $caseCreated = create_case_from_transaction($ready_invoice['id'], $creditor['id'], true);
        if($caseCreated){
            $newCaseCount++;
        }
    }

    $s_sql = "UPDATE creditor SET last_create_case_date = NOW() WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    echo $newCaseCount ." ".$formText_NewCasesCreated_output;
} else {
    echo $formText_MissingProcessId_output;
}
?>
