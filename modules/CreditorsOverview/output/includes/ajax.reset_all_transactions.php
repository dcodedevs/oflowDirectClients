<?php
$creditor_id = $_POST['creditor_id'];

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditor_id));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){
	$sql = "SELECT collecting_cases.id FROM collecting_cases WHERE creditor_id = ?";
	$o_query = $o_main->db->query($sql, array($creditor['id']));
	$collectingCaseCount = $o_query ? $o_query->num_rows() : 0;

	$sql = "SELECT collecting_company_cases.id FROM collecting_company_cases WHERE creditor_id = ?";
	$o_query = $o_main->db->query($sql, array($creditor['id']));
	$collectingCompanyCaseCount = $o_query ? $o_query->num_rows() : 0;

	if($collectingCaseCount == 0 && $collectingCompanyCaseCount == 0) {
		$sql = "DELETE creditor_transactions FROM creditor_transactions WHERE creditor_id = ?";
		$o_query = $o_main->db->query($sql, array($creditor['id']));
		if($o_query) {
			$sql = "UPDATE creditor SET lastImportedDateTimestamp = NULL, lastImportedDate = NULL WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($creditor['id']));
		}
	} else {
		echo 'Creditor already has cases';
	}
} else {
	echo 'Creditor not found';
}
?>
