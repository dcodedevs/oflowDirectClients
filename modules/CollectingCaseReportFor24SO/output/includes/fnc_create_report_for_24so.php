<?php
if(!function_exists("create_report_for_24so")){
	function create_report_for_24so($date){
		global $o_main;
		$s_sql = "SELECT * FROM creditor WHERE content_status < 2";
		$o_query = $o_main->db->query($s_sql);
		$creditors = $o_query ? $o_query->result_array() : array();

		foreach($creditors as $creditor) {
			$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
			LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
			WHERE IFNULL(cccl.billing_report_id, 0) = 0
			AND DATE(IFNULL(cccl.created, '0000-00-00')) <= '".date("Y-m-d", strtotime($date))."'
			AND cc.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND
			(cccl.fees_status = 1 OR ((cccl.sent_to_external_company = 1 OR cccl.performed_action = 5) AND cccl.sending_status = 1))";
			$o_query = $o_main->db->query($s_sql);
			$claimletters_created_on_date = $o_query ? $o_query->result_array() : array();

			$s_sql = "SELECT cc.* FROM collecting_cases cc
			LEFT OUTER JOIN collecting_cases_claim_letter cccl ON cccl.case_id = cc.id
			WHERE (DATE(IFNULL(cc.stopped_date, '0000-00-00')) <> '0000-00-00' AND DATE(IFNULL(cc.stopped_date, '0000-00-00')) <= '".date("Y-m-d", strtotime($date))."')
			AND IFNULL(cc.billing_report_id, 0) = 0 AND cc.approved_for_report = 1
			AND cc.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND IFNULL(cccl.fees_status, 0) = 0
			GROUP BY cc.id";
			$o_query = $o_main->db->query($s_sql);
			$closed_cases = $o_query ? $o_query->result_array() : array();

			if(count($claimletters_created_on_date) > 0 || count($closed_cases) > 0) {
				$report_id = 0;
				$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE DATE(date) = '".date("Y-m-d", strtotime($date))."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$reported_entry = $o_query ? $o_query->row_array() : array();
				if(!$reported_entry) {
					$s_sql = "INSERT INTO collecting_cases_report_24so SET created = NOW(), creditor_id = '".$o_main->db->escape_str($creditor['id'])."', date='".date("Y-m-d", strtotime($date))."'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query){
						$report_id = $o_main->db->insert_id();
					}
				} else {
					$report_id = $reported_entry['id'];
				}
				if($report_id > 0) {
					foreach($claimletters_created_on_date as $claimletter_created_on_date){
						if($claimletter_created_on_date['fees_status'] == 1
						|| (($claimletter_created_on_date['sent_to_external_company'] || $claimletter_created_on_date['performed_action'] == 5) && $claimletter_created_on_date['sending_status'])){
							$s_sql = "UPDATE collecting_cases_claim_letter SET
							billing_report_id = '".$o_main->db->escape_str($report_id)."'
							WHERE id = '".$o_main->db->escape_str($claimletter_created_on_date['id'])."'";
							$o_query = $o_main->db->query($s_sql);
						}
					}

					foreach($closed_cases as $closed_case){
						$s_sql = "UPDATE collecting_cases SET
						billing_report_id = '".$o_main->db->escape_str($report_id)."'
						WHERE id = '".$o_main->db->escape_str($closed_case['id'])."'";
						$o_query = $o_main->db->query($s_sql);
					}

					$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE billing_report_id = ? AND IFNULL(fees_status, 0) = 1";
					$o_query = $o_main->db->query($s_sql, $report_id);
					$lettersSentWithoutFeeCount = $o_query ? $o_query->num_rows(): 0;

					$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
					LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
					WHERE cc.billing_report_id = ? AND IFNULL(cc.fees_forgiven, 0) = 1 AND IFNULL(cccl.fees_status, 0) = 0";
					$o_query = $o_main->db->query($s_sql, $report_id);
					$feesForgivenCount = $o_query ? $o_query->num_rows(): 0;

					$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount
					FROM collecting_cases_claim_letter cccl
					LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
					LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
					WHERE cc.billing_report_id = ? AND IFNULL(cc.fees_forgiven, 0) = 0 GROUP BY cc.id";
					$o_query = $o_main->db->query($s_sql, array($report_id));
					$cases = $o_query ? $o_query->result_array() : array();
					$total_fee_payed = 0;
					$total_interest_payed = 0;
					foreach($cases as $case) {
						$total_fee_payed+=$case['payed_fee_amount'];
						$total_interest_payed+=$case['payed_interest_amount'];
					}

					$total_printed = 0;

					$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount
					FROM collecting_cases_claim_letter cccl
					LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
					LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
					WHERE cccl.billing_report_id = ? AND IFNULL(cccl.performed_action, 0) = 0 AND cccl.sent_to_external_company = 1 AND cccl.sending_status = 1";
					$o_query = $o_main->db->query($s_sql, array($report_id));
					$total_printed = $o_query ? $o_query->num_rows() : 0;
					
					$ehfAmount = 0;
					$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount
					FROM collecting_cases_claim_letter cccl
					LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
					LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
					WHERE cccl.billing_report_id = ? AND IFNULL(cccl.performed_action, 0) = 5 AND cccl.sending_status = 1";
					$o_query = $o_main->db->query($s_sql, array($report_id));
					$ehfAmount = $o_query ? $o_query->num_rows() : 0;

					$s_sql = "UPDATE collecting_cases_report_24so SET
					fee_payed_amount = '".$o_main->db->escape_str($total_fee_payed)."',
					interest_payed_amount = '".$o_main->db->escape_str($total_interest_payed)."',
					printed_amount = '".$o_main->db->escape_str($total_printed)."',
					sent_without_fees_amount = '".$o_main->db->escape_str($lettersSentWithoutFeeCount)."',
					fees_forgiven_amount = '".$o_main->db->escape_str($feesForgivenCount)."',
					ehf_amount = '".$o_main->db->escape_str($ehfAmount)."'
					WHERE id = '".$o_main->db->escape_str($report_id)."'";
					$o_query = $o_main->db->query($s_sql);
				}
			}
		}
	}
}
?>
