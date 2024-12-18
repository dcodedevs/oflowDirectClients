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
			if($case['approved_for_report']) {
				$s_sql = "UPDATE collecting_cases SET payed_fee_amount = 0, payed_interest_amount = 0, approved_for_report = 0, fees_forgiven = 0 WHERE id = '".$o_main->db->escape_str($case['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query){
					$approvedCount++;
				}
			}
			echo $approvedCount." ".$formText_CasesWereUnapprovedForReport_output;
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
