<?php 
include_once(__DIR__."/fnc_process_open_cases_for_tabs.php");

$creditor_id = $_POST['creditor_id'];

$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE creditor_id = ? AND open = 1";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$source_id = 1;
process_open_cases_for_tabs($creditor_id, $source_id);
?>