<?php
include_once(__DIR__."/fnc_generate_pdf.php");
include_once(__DIR__."/fnc_calculate_interest.php");
$caseId = $_POST['caseId'];
if($caseId > 0){
	$onhold_sql = " AND (onhold_by_creditor is null OR onhold_by_creditor = 0)";
	if($skip_to_step > 0){
	    $onhold_sql = "";
	}
	$collectingcase_status_sql = " AND IFNULL(case_closed_date, '0000-00-00') = '0000-00-00' ";

    $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ? ".$collectingcase_status_sql.$onhold_sql."";
    $o_query = $o_main->db->query($s_sql, array($caseId));
    $case = $o_query ? $o_query->row_array() : array();
	if($case){
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

		$result = generate_pdf($caseId, 1);
		if(count($result['errors']) > 0){
			foreach($result['errors'] as $error){
				echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseId." ".$error."</br>";
			}
		} else {
			echo $formText_SuccessfullyCreatedRestNote_output;
		}
	} else {
		echo $formText_NotActiveCase_output;
	}
}
?>
