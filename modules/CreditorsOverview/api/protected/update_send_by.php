<?php
$creditor_id = $v_data['params']['creditor_id'];
$debitor_id = $v_data['params']['debitor_id'];
$send_by = $v_data['params']['send_by'];
$username = $v_data['params']['username'];

if($debitor_id > 0 && $creditor_id > 0) {
	$s_sql = "UPDATE customer SET updated=NOW(), updatedBy = ?, overview_send_by = ? WHERE id = ? and creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($username, $send_by, $debitor_id, $creditor_id));
}
?>
