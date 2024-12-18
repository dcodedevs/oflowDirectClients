<?php
$creditor_id = $v_data['params']['creditor_id'];
$bookaccount_for_reminder = $v_data['params']['bookaccount_for_reminder'];
$bookaccount_for_interest = $v_data['params']['bookaccount_for_interest'];
$username = $v_data['params']['username'];

if($bookaccount_for_reminder > 0) {
	$s_sql = "UPDATE creditor SET updated=NOW(), updatedBy = ?, reminder_bookaccount = ? WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($username, $bookaccount_for_reminder, $creditor_id));
}
if($bookaccount_for_interest > 0) {
	$s_sql = "UPDATE creditor SET updated=NOW(), updatedBy = ?, interest_bookaccount = ? WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($username, $bookaccount_for_interest, $creditor_id));
}
?>
