<?php 
$caseId = $_POST['caseId'];

$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($caseId));
$case = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());

$casesToGenerate = array();
$manualProcessing = 1;
$creditorId = $creditor['id'];
$collecting_case_id = $case['id'];
include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases_collecting.php");

?>