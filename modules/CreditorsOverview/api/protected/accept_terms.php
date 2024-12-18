<?php
include(__DIR__."/../languagesOutput/default.php");
$_POST = $v_data['params']['post'];
$username = $v_data['params']['username'];

$creditor_id = $_POST['creditor_id'];
$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = $o_query ? $o_query->row_array() : array();

$read_agreement = $_POST['read_agreement'];

if($creditor){
	if($read_agreement) {
		$read_agreement_sql = "";
		$filename = "";
		$create_agreement_file = __DIR__ . '/../../api/protected/fnc_create_agreement_file.php';
		if (file_exists($create_agreement_file)) {
			include $create_agreement_file;
			$result = create_agreement_file($creditor['id']);
			$filename = $result['file'];
		}
		$read_agreement_sql = ", collecting_agreement_accepted_by = '".sanitize_escape($username)."', collecting_agreement_accepted_date = NOW(), collecting_agreement_file = '".sanitize_escape($filename)."'";
		
		$s_sql = "UPDATE creditor SET
		updated = now(),
		updatedBy= ?,
		force_approve_terms = 0".$read_agreement_sql."
		WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($username, $creditor['id']));
		if($o_query){
			$v_return['status'] = 1;
		} else {
			$v_return['error'] = $formText_ErrorSaving_output;
		}
	} else {
		$v_return['error'] = $formText_AgreeToAgreement_output;
	}
} else {
	$v_return['error'] = $formText_MissingCreditor_output;
}

?>
