<?php
$caseId = $_POST['case_id'] ? $_POST['case_id'] : 0;
$currency = $_POST['currency'] ? $_POST['currency'] : 0;
if ($caseId) {
	$sql = "UPDATE collecting_company_cases SET
	updated = now(),
	updatedBy='".$variables->loggID."',
	currency='".$o_main->db->escape_str($currency)."'
	WHERE id = '".$o_main->db->escape_str($caseId)."'";

	$o_query = $o_main->db->query($sql);
	$insert_id = $caseId;
	$fw_redirect_url = $_POST['redirect_url'];
}
?>
