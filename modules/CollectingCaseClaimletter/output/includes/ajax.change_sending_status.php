<?php
$letter_id = $_POST['letter_id'];
$status = $_POST['status'];
if($letter_id > 0){
    $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($letter_id));
    $letter = ($o_query ? $o_query->row_array() : array());
	if($letter){
	    $s_sql = "UPDATE collecting_cases_claim_letter SET updated =NOW(), updatedBy = ?, sending_status = ? WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $status, $letter_id));
	}
}
?>
