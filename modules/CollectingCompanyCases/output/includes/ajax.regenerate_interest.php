<?php
if($moduleAccesslevel > 10)
{
	include(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_calculate_interest.php");
	if(isset($_POST['case_id']))
	{

        $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
        $case = $o_query ? $o_query->row_array() : array();
		if($case){

			$noInterestError = false;
			$s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_company_case_id = ? ";
			$o_query = $o_main->db->query($s_sql, array($case['id']));

			$currentClaimInterest = 0;
			$interestArray = calculate_interest(array(), $case);
			$totalInterest = 0;
			foreach($interestArray as $interest_index => $interest) {
				$interest_index_array = explode("_", $interest_index);
				$claimline_id = intval($interest_index_array[2]);

				$interestRate = $interest['rate'];
				$interestAmount = $interest['amount'];
				$interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
				$interestTo = date("Y-m-d", strtotime($interest['dateTo']));

				$s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
				date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."',
				collecting_company_cases_claim_line_id = '".$o_main->db->escape_str($claimline_id)."'";
				$o_query = $o_main->db->query($s_sql, array());
				$totalInterest += $interestAmount;
			}

			$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."' AND claim_type = 8 ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($case['id']));
			$interest_claim_line = ($o_query ? $o_query->row_array() : array());
			if($interest_claim_line) {
				$s_sql = "UPDATE collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
				collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."'
				WHERE id = '".$o_main->db->escape_str($interest_claim_line['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			} else {
				$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
				collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."', claim_type = 8, name= '".$o_main->db->escape_str($formText_Interest_output)."'";
				$o_query = $o_main->db->query($s_sql);
			}
		} else {
			$fw_error_msg[] = $formText_MissingCase_output;
		}
	}
}
