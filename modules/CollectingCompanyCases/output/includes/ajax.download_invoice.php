<?php
$transaction_id = $_POST['transaction_id'];
$creditor_id = $_POST['creditor_id'];

$s_sql = "SELECT ci.*, CONCAT_WS(' ', cred.companyname) as creditorName FROM creditor_transactions ci
LEFT JOIN creditor cred ON cred.id = ci.creditor_id
WHERE ci.id = ? AND cred.id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction_id, $creditor_id));
$invoice = $o_query ? $o_query->row_array() : array();

if($invoice) {
	$s_sql = "select * from creditor where id = ?";
	$o_query = $o_main->db->query($s_sql, array($invoice['creditor_id']));
	$creditorData = $o_query ? $o_query->row_array() : array();

	require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
	$v_config = array(
		'ownercompany_id' => 1,
		'identityId' => $creditorData['entity_id'],
		'creditorId' => $creditorData['id'],
		'o_main' => $o_main
	);
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && 0 < $o_query->num_rows())
	{
		$v_int_session = $o_query->row_array();
		$v_config['session_id'] = $v_int_session['session_id'];
	}

	$api = new Integration24SevenOffice($v_config);
	if($api->error == "") {
		$data = array("invoice_id"=>$invoice['invoice_nr']);
		$fileText = $api->get_invoice_pdf($data);
		$fw_return_data = base64_encode($fileText);
	} else {
		$fw_error_msg[] = ' Error connecting to integration';
	}
} else {
	$fw_error_msg[] = 'Missing invoice';
}

?>
