<?php
$transaction_id= $v_data['params']['transaction_id'];
$process_id= $v_data['params']['process_id'];
$username= $v_data['params']['username'];
$languageID = $v_data['params']['languageID'];
$case_choice = intval($v_data['params']['case_choice']);
$company_case_id = intval($v_data['params']['company_case_id']);
$sign_agreement = intval($v_data['params']['sign_agreement']);


if($sign_agreement) {
	$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($transaction_id));
	$transaction = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
	$creditor = ($o_query ? $o_query->row_array() : array());
	if($creditor){
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
		updatedBy= ?".$read_agreement_sql."
		WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($username, $creditor['id']));
	}
}
require(__DIR__."/../../output/includes/fnc_move_transaction_to_collecting.php");
$v_return = move_transaction_to_collecting($transaction_id, $process_id, $username);

?>
