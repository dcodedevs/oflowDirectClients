<?php
$languageID = $variables->languageID;
if(count($_POST['approve_for_report'])> 0){
	foreach($_POST['approve_for_report'] as $caseId){
		$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseId));
		$case = ($o_query ? $o_query->row_array() : array());
		if($case){
			ob_start();
			$approvedCount = 0;
			if(!$case['approved_for_report']) {

				$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
				WHERE IFNULL(cccl.fees_status, 0) = 0
				AND cccl.case_id = '".$o_main->db->escape_str($case['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$created_claimletters = $o_query ? $o_query->result_array() : array();

				$payed_fee_amount = str_replace(" ", "", str_replace(",", ".",$_POST['payed_fee_amount'][$case['id']]));
				$payed_interest_amount = str_replace(" ", "", str_replace(",", ".",$_POST['payed_interest_amount'][$case['id']]));
				$fees_forgiven = 0;
				if(count($created_claimletters) > 0) {
					if(($payed_fee_amount+$payed_interest_amount) == 0) {
						$fees_forgiven = 1;
					}
				}

				$s_sql = "UPDATE collecting_cases SET payed_fee_amount = ?, payed_interest_amount = ?, approved_for_report = 1, fees_forgiven = ? WHERE id = '".$o_main->db->escape_str($case['id'])."'";
				$o_query = $o_main->db->query($s_sql, array($payed_fee_amount, $payed_interest_amount, $fees_forgiven));
				if($o_query){
					$approvedCount++;
				}
			} else {
				echo $formText_CaseAlreadyApproved_output." ".$case['id']."<br/>";
			}
			echo $approvedCount." ".$formText_CasesWereApprovedForReport_output;
			$result_output = ob_get_contents();
			$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
			ob_end_clean();
			echo $result_output;
		} else {
			echo $formText_MissingCase_output;
		}
	}
} else {
	echo $formText_CasesNotSelected_output;
}
?>
