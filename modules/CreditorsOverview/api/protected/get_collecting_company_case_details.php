<?php
$case_id = $v_data['params']['case_id'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['accounturl'];

if($case_id) {
	$sql = "SELECT * FROM collecting_company_cases WHERE id = $case_id";
	$o_query = $o_main->db->query($sql);
	$caseData = $o_query ? $o_query->row_array() : array();

	$sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
	$o_query = $o_main->db->query($sql);
	$debitorData = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
	LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
	WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
	ORDER BY cccl.claim_type ASC, cccl.created DESC";
	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
	$claims = ($o_query ? $o_query->result_array() : array());
	$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
	$payments = ($o_query ? $o_query->result_array() : array());

	$totalSumPaid = 0;

	foreach($payments as $payment) {
		$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
		$o_query = $o_main->db->query($s_sql);
		$transactions = ($o_query ? $o_query->result_array() : array());
		foreach($transactions as $transaction) {
			$totalSumPaid += $transaction['amount'];
		}
	}

	$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
	$v_claim_letters = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT * FROM creditor_collecting_company_chat WHERE creditor_id = ? AND collecting_company_case_id=?
    ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id'], $caseData['id']));
    $selected_chat_messages = ($o_query ? $o_query->result_array() : array());


	$v_return['v_claim_letters'] = $v_claim_letters;
	$v_return['claims'] = $claims;
	$v_return['caseData'] = $caseData;
	$v_return['totalSumPaid'] = $totalSumPaid;
	$v_return['selected_chat_messages'] = $selected_chat_messages;
	$v_return['debitorData'] = $debitorData;	

	$v_return['status'] = 1;
}
?>
