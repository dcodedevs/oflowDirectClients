<?php
if($autoreportcreditor) {
	
	$s_sql = "SELECT ownercompany.* FROM  ownercompany";
	$o_query = $o_main->db->query($s_sql);
	$ownercompany = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT creditor.* FROM  creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['creditorId']));
	$creditor = ($o_query ? $o_query->row_array() : array());
	if($_POST['closed']){
		$sql = "SELECT arl.id FROM collecting_company_cases ccc
		LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
		LEFT OUTER JOIN autoreportcreditor_lines arl ON arl.case_id = ccc.id
		WHERE ccc.content_status < 2
		AND IFNULL(ccc.case_closed_date, '0000-00-00') <> '0000-00-00' AND arl.id is not null AND IFNULL(arl.case_closed_autoreport_id, 0) = 0
		AND ccc.creditor_id = ? ";
		$result = $o_main->db->query($sql, array($creditor['id']));
		$collecting_company_cases = $result ? $result->result_array(): array();
		if(count($collecting_company_cases) > 0){
			$s_sql = "INSERT INTO autoreportcreditor_report SET created = NOW(), autoreportcreditor_id = ?, closed_report = 1";
			$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['id']));
			if($o_query) {
				$report_id = $o_main->db->insert_id();
				foreach($collecting_company_cases as $autoreportcreditor_line){
					$s_sql = "UPDATE autoreportcreditor_lines SET updated = NOW(), case_closed_autoreport_id = ? WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($report_id, $autoreportcreditor_line['id']));
				}
			}
		}
	} else {
		$sql = "SELECT ccc.*, deb.creditor_customer_id as customer_nr, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName FROM collecting_company_cases ccc
		LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
		WHERE ccc.content_status < 2
		AND IFNULL(ccc.case_closed_date, '0000-00-00') = '0000-00-00'
		AND ccc.creditor_id = ? ";
		$result = $o_main->db->query($sql, array($creditor['id']));
		$collecting_company_cases = $result ? $result->result_array(): array();

		foreach($collecting_company_cases as $collecting_company_case) {
			if($collecting_company_case['collecting_case_created_date'] != "0000-00-00" && $collecting_company_case['collecting_case_created_date'] != "" && strtotime($collecting_company_case['collecting_case_created_date']) < time()) {
				$sql = "SELECT * FROM autoreportcreditor_lines WHERE autoreportcreditor_id = ? AND case_id = ?";
				$result = $o_main->db->query($sql, array($autoreportcreditor['id'], $collecting_company_case['id']));
				$autoreportcreditor_line = $result ? $result->row_array(): array();
				if(!$autoreportcreditor_line) {
					$bankaccount = $ownercompany['companyaccount'];
					$kidnumber = $collecting_company_case['kid_number'];
					$debitor_customer_nr = $collecting_company_case['customer_nr'];

					$sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = ?";
					$result = $o_main->db->query($sql, array($collecting_company_case['id']));
					$creditor_transactions = $result ? $result->result_array(): array();
					$invoice_numbers = array();
					foreach($creditor_transactions as $creditor_transaction) {
						$invoice_numbers[] = $creditor_transaction['invoice_nr'];
					}

					$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
					LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
					WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
					ORDER BY cccl.claim_type ASC, cccl.created DESC";
					$o_query = $o_main->db->query($s_sql, array($collecting_company_case['id']));
					$claims = ($o_query ? $o_query->result_array() : array());

					$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
					$o_query = $o_main->db->query($s_sql, array($collecting_company_case['id']));
					$payments = ($o_query ? $o_query->result_array() : array());

					$totalSumPaid = 0;
					$totalSumDue = 0;
					foreach($claims as $claim) {
						if(!$claim['payment_after_closed']) {
							$totalSumDue += $claim['amount'];
						}
					}
					foreach($payments as $payment) {
						$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
						$o_query = $o_main->db->query($s_sql);
						$transactions = ($o_query ? $o_query->result_array() : array());
						foreach($transactions as $transaction) {
							$totalSumPaid += $transaction['amount'];
						}
					}

					$total_outstanding_oflow = number_format($totalSumDue - $totalSumPaid, 2, ".", "");

					$sql = "INSERT INTO autoreportcreditor_lines SET created = NOW(), autoreportcreditor_id = ?, case_id = ?, bankaccount = ?,
					 kidnumber = ?, debitor_customer_nr = ?, invoice_numbers = ?, total_outstanding_oflow = ?, reported_to_creditor_date = '0000-00-00'";
					$result = $o_main->db->query($sql, array($autoreportcreditor['id'], $collecting_company_case['id'],
					$bankaccount, $kidnumber, $debitor_customer_nr, implode(",", $invoice_numbers), $total_outstanding_oflow));
				}
			}
		}
	}
}
?>
