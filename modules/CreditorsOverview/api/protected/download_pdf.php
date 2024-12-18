<?php
$username = $v_data['params']['username'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$invoice_id = $v_data['params']['invoice_id'];
if($creditor_filter > 0){

	$s_sql = "SELECT ci.*, CONCAT_WS(' ', cred.companyname) as creditorName FROM creditor_transactions ci
	LEFT JOIN creditor cred ON cred.id = ci.creditor_id
	WHERE ci.id = ? AND cred.id = ?";
	$o_query = $o_main->db->query($s_sql, array($invoice_id, $creditor_filter));
	$invoice = $o_query ? $o_query->row_array() : array();
} else {
	$s_sql = "select * from customer where id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$creditor_customer = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT ci.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as creditorName FROM creditor_transactions ci
	LEFT JOIN creditor cred ON cred.id = ci.creditor_id
	LEFT JOIN customer c ON cred.customer_id = c.id
	WHERE ci.id = ? AND c.id = ?";
	$o_query = $o_main->db->query($s_sql, array($invoice_id, $creditor_customer['id']));
	$invoice = $o_query ? $o_query->row_array() : array();
}
if($invoice){
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
		$v_return['invoiceFileData'] = base64_encode($fileText);
		$v_return['invoiceNr'] = $invoice['invoice_nr'];
	} else {
		$v_return['error'] = ' Error connecting to integration';
	}
} else {
	$v_return['error'] = $invoice_id." ". $creditor_customer['creditor_id']. 'Missing invoice';
}
?>
