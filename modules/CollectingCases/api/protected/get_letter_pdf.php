<?php
$invoice_nr = $v_data['params']['invoice_nr'];
$client_id = $v_data['params']['client_id'];
$letter_id = $v_data['params']['letter_id'];

if("" != $invoice_nr && "" != $client_id)
{
	$s_sql = "select * from creditor where 24sevenoffice_client_id = '".$o_main->db->escape_str($client_id)."'";
	$o_query = $o_main->db->query($s_sql);
	$creditor = $o_query ? $o_query->row_array() : array();
	if($creditor)
	{
		$s_sql = "select * from creditor_transactions where invoice_nr = '".$o_main->db->escape_str($invoice_nr)."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND collectingcase_id > 0";
		$o_query = $o_main->db->query($s_sql);
		$creditor_transactions = $o_query ? $o_query->result_array() : array();
		foreach($creditor_transactions as $creditor_transaction)
		{
			$s_sql = "select * from collecting_cases where id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collectingcase_id']));
			$case = $o_query ? $o_query->row_array() : array();

			$s_sql = "select * from collecting_cases_claim_letter where id = '".$o_main->db->escape_str($letter_id)."' AND case_id = '".$o_main->db->escape_str($case['id'])."' ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql);
			$lastSentLetter = $o_query ? $o_query->row_array() : array();

			if(isset($lastSentLetter['id']) && 0 < $lastSentLetter['id'] && !empty($lastSentLetter['pdf']))
			{
				$s_file = BASEPATH.$lastSentLetter['pdf'];
				if(is_file($s_file))
				{
					$filenameArray	= explode("/", $s_file);
					$filename		= $s_file;
					if(count($filenameArray) > 1)
					{
						$filename	= end($filenameArray);
					}
					$v_return['status'] = 1;
					$v_return['file'] = $filename;
					$v_return['file_content'] = base64_encode(file_get_contents($s_file));

					//header("Content-Disposition: attachment; filename=".$filename);
					//header("Content-Length: " . filesize($s_file));
					//header("Content-Type: application/octet-stream;");

					//readfile($s_file);
					//exit;
				} else {
					$v_return['message'] = "file_not_found";
				}
			}
		}
	} else {
	    $v_return['message'] = "Client not registered";	
	}
} else {
    $v_return['message'] = "Missing creditor id or invoice nr";
}
