<?php 
if($variables->developeraccess > 5) {
    $s_sql = "SELECT collecting_cases.* FROM collecting_cases
	JOIN creditor_transactions ct ON ct.collectingcase_id = collecting_cases.id
	WHERE ct.open = 0 AND (collecting_cases.stopped_date = '0000-00-00 00:00:00' OR collecting_cases.stopped_date IS null)";
	$o_query = $o_main->db->query($s_sql);
	$closed_cases_without_stopped_date_count = ($o_query ? $o_query->num_rows() : 0);
    echo $closed_cases_without_stopped_date_count ." ".$formText_ClosedCasesWithoutStoppedDate_output;
}
?>