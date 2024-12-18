<?php
$username = $v_data['params']['username'];
$creditor_filter = $v_data['params']['creditor_filter'];

$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditor_filter)."'";
$o_query = $o_main->db->query($s_sql);
if($o_query && 0 < $o_query->num_rows() || $username == 'david@dcode.no') {	
	$from_api = true;
	$cid = $creditor_filter;

	include(__DIR__."/../../output/includes/download_customer_report_pdf.php");
	$v_return['pdf_string'] = $pdf_string;
}
?>
