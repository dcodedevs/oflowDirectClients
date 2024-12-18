<?php
$letter_id = $_POST['letter_id'];
$action = $_POST['action'];
if($letter_id > 0){
    $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($letter_id));
    $letter = ($o_query ? $o_query->row_array() : array());
	if($letter){
	    $s_sql = "UPDATE collecting_cases_claim_letter SET updated =NOW(), updatedBy = ?, sending_action = ? WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $action, $letter_id));
	}
}
?>
