<?php
$creditor_id = $_POST['creditor_id'];

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditor_id));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){
	$sql = "DELETE creditor_transactions FROM creditor_transactions WHERE creditor_id = ? AND collectingcase_id is null and collecting_company_case_id is null";
	$o_query = $o_main->db->query($sql, array($creditor['id']));
	if($o_query){
		$sql = "UPDATE creditor SET lastImportedDateTimestamp = NULL, lastImportedDate = NULL WHERE id = ?";
		$o_query = $o_main->db->query($sql, array($creditor['id']));
		if($o_query){
			$creditorId = $creditor['id'];
			include(__DIR__."/import_scripts/import_cases2.php");
		}
	}
}
?>
