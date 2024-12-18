<?php
$casesToGenerate = $_POST['casesToGenerate'];

$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND (sending_status = 4)";
$o_query = $o_main->db->query($s_sql);
$cases = $o_query ? $o_query->result_array() : array();
$created_letters = 0;
if(count($cases) > 0){
	foreach($cases as $created_letter)
	{
		if(in_array($created_letter['id'], $casesToGenerate)) {
			$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), performed_date = NOW(), performed_action = 3, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			$o_query = $o_main->db->query($s_sql);
		}
	}
}
if($created_letters > 0){
	$fw_return_data = array(
		'status' => 1
	);
} else {
	$fw_error_msg[] = $formText_Moved_output;
}
?>
