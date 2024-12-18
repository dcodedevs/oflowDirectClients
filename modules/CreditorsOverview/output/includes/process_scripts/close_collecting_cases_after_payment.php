<?php

$s_sql = "SELECT * FROM collecting_company_cases WHERE case_closed_date = '0000-00-00'";
$o_query = $o_main->db->query($s_sql, array());
$cases = $o_query ? $o_query->result_array() : array();
foreach($cases as $case) {
	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
	$creditor = ($o_query ? $o_query->row_array() : array());

	// $s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = ? AND creditor_id = ?";
	// $o_query = $o_main->db->query($s_sql, array($case['id'], $creditor['id']));
	// $transactions = ($o_query ? $o_query->result_array() : array());


	$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
	LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
	WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
	ORDER BY cccl.claim_type ASC, cccl.created DESC";
	$o_query = $o_main->db->query($s_sql, array($case['id']));
	$claims = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($case['id']));
	$payments = ($o_query ? $o_query->result_array() : array());

	$totalSumPaid = 0;
	$totalSumDue = 0;

	foreach($payments as $payment) {
		$totalSumPaid += $payment['amount'];
	}

	foreach($claims as $claim) {
		$totalSumDue += $claim['amount'];
	}
	$totalSumDueAfterPayment = number_format($totalSumDue - $totalSumPaid, 2, ".", "");

	if($totalSumDueAfterPayment <= 0) {
		$s_sql = "UPDATE collecting_company_cases SET updated=NOW(), case_closed_date = NOW(), case_closed_reason = 0 WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($case['id']));
	} else if($totalSumDueAfterPayment - $collectingMaximumAmountForgiveTooLittlePayed <= 0){
		$s_sql = "UPDATE collecting_company_cases SET updated=NOW(), case_closed_date = NOW(), case_closed_reason = 1, case_closed_not_payed_amount = ? WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($totalSumDueAfterPayment, $case['id']));
	} else {
		if($totalSumDueAfterPayment - $case['case_closed_not_payed_amount'] > 0) {
			if($case['case_closed_date'] != "0000-00-00" && $case['case_closed_date'] != ""){
				$s_sql = "UPDATE collecting_company_cases SET updated=NOW(), case_closed_date = '0000-00-00', case_closed_reason = 0, collectingcase_progress_type = 2 WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($case['id']));
			}
		}
	}
}
?>
