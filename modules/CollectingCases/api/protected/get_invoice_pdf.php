<?php
$ip = $v_data['params']['ip'];
$code = $v_data['params']['code'];
$invoice_id = $v_data['params']['invoice_id'];

$s_sql = "select * from collecting_cases_debitor_codes_log where ip = ? AND successful = 0 AND created BETWEEN  DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')  AND  DATE_FORMAT(NOW(), '%Y-%m-%d %H:59:59')";
$o_query = $o_main->db->query($s_sql, array($ip));
$attempts = $o_query ? $o_query->result_array() : array();

require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
if(count($attempts) < 3){
	$s_sql = "select * from collecting_cases_debitor_codes_log where ip = ? AND successful = 1 AND created BETWEEN  DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')  AND  DATE_FORMAT(NOW(), '%Y-%m-%d %H:59:59')";
	$o_query = $o_main->db->query($s_sql, array($ip));
	$successful_logins = $o_query ? $o_query->result_array() : array();
	if(count($successful_logins) < 50){
	    $s_sql = "INSERT INTO collecting_cases_debitor_codes_log SET code_used = ?, successful = 0, ip = ?, created = NOW()";
	    $o_query = $o_main->db->query($s_sql, array($code, $ip));
	    if($o_query){
	        $log_id = $o_main->db->insert_id();
	        $s_sql = "select * from collecting_cases_debitor_codes where code = ? AND expiration_time > NOW()";
	        $o_query = $o_main->db->query($s_sql, array($code));
	        $key_item = $o_query ? $o_query->row_array() : array();
	        if($key_item) {
	            $s_sql = "UPDATE collecting_cases_debitor_codes_log SET successful = 1 WHERE id = ?";
	            $o_query = $o_main->db->query($s_sql, array($log_id));

	            $s_sql = "select * from customer where id = ?";
	            $o_query = $o_main->db->query($s_sql, array($key_item['customer_id']));
	            $customer = $o_query ? $o_query->row_array() : array();

	            $s_sql = "SELECT ci.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as creditorName FROM creditor_transactions ci
	            LEFT JOIN creditor cred ON cred.id = ci.creditor_id
	            LEFT JOIN customer c ON cred.customer_id = c.id
	            WHERE ci.id = ? AND ci.external_customer_id = ?";
	            $o_query = $o_main->db->query($s_sql, array($invoice_id, $customer['creditor_customer_id']));
	            $invoice = $o_query ? $o_query->row_array() : array();
				if($invoice){
					// $v_return['invoiceFileData'] = base64_encode(file_get_contents(__DIR__."/../../../../".$invoice['invoiceFile']));
					// $v_return['invoiceNr'] = $invoice['invoice_nr'];
					if($key_item['collecting_cases_id'] > 0){
						if($invoice['collectingcase_id'] == $key_item['collecting_cases_id']){
			                $s_sql = "select * from creditor where id = ?";
			                $o_query = $o_main->db->query($s_sql, array($invoice['creditor_id']));
			                $creditorData = $o_query ? $o_query->row_array() : array();

							$v_config = array(
				                'ownercompany_id' => 1,
				                'identityId' => $creditorData['entity_id'],
				                'creditorId' => $creditorData['id'],
				                'o_main' => $o_main
				            );
							$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' ORDER BY created DESC";
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
								if($fileText != ""){
						            $v_return['invoiceFileData'] = base64_encode($fileText);
									$v_return['invoiceNr'] = $invoice['invoice_nr'];
								} else {
						        	$v_return['error'] = 'Missing invoice file';
								}
							} else {
					            $v_return['error'] = 'Error connecting to integration';
							}
						}
					} else if ($key_item['collecting_company_case_id'] > 0) {
						if($invoice['collecting_company_case_id'] == $key_item['collecting_company_case_id']){
			                $s_sql = "select * from creditor where id = ?";
			                $o_query = $o_main->db->query($s_sql, array($invoice['creditor_id']));
			                $creditorData = $o_query ? $o_query->row_array() : array();
							$v_config = array(
				                'ownercompany_id' => 1,
				                'identityId' => $creditorData['entity_id'],
				                'creditorId' => $creditorData['id'],
				                'o_main' => $o_main
				            );
							$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' ORDER BY created DESC";
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
					            $v_return['error'] = 'Error connecting to integration';
							}
						}
					}

				} else {
		            $v_return['error'] = 'Error finding file';
				}
	        } else {
	            $v_return['error'] = 'Wrong/expired code';
	        }
	    }
	} else {
	    $v_return['error'] = "Too many requests. Try later.";
	}
} else {
    $v_return['error'] = "Too many wrong requests. You have been suspended for 1 hour.";
}
?>
