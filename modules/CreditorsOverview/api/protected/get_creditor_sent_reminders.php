<?php
$creditor_id = $v_data['params']['creditor_id'];
$date = $v_data['params']['date'];
$customer_id = $v_data['params']['customer_id'];


$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	if($customer_id > 0) {
		$sql_where = " AND cc.debitor_id = '".$o_main->db->escape_str($customer_id)."'";
	}
	$per_page = 100;
	$offset = 0;
	if($page > 1){
		$offset = ($page - 1) * $per_page;
	}
	$pager = " LIMIT ".$per_page." OFFSET ".$offset;
	$s_sql = "SELECT cccl.*, c.invoiceEmail, concat_ws(' ', c.name, c.middlename, c.lastname)  as customerName
	FROM collecting_cases_claim_letter cccl
	JOIN collecting_cases cc ON cc.id = cccl.case_id
	JOIN customer c ON c.id = cc.debitor_id
	WHERE cc.creditor_id = ? AND cccl.sending_status = 1 AND IFNULL(cccl.performed_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'".$sql_where."
	ORDER BY cccl.performed_date DESC".$pager;
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$sent_reminders = ($o_query ? $o_query->result_array() : array());
	
	$s_sql = "SELECT c.id, concat_ws(' ', c.name, c.middlename, c.lastname)  as customerName
	FROM collecting_cases_claim_letter cccl
	JOIN collecting_cases cc ON cc.id = cccl.case_id
	JOIN customer c ON c.id = cc.debitor_id
	WHERE cc.creditor_id = ? AND cccl.sending_status = 1 AND IFNULL(cccl.performed_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
	GROUP BY c.id";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$customers = ($o_query ? $o_query->result_array() : array());

	$v_return['creditor'] = $creditor;
	$v_return['sent_reminders'] = $sent_reminders;
	$v_return['customers'] = $customers;
	
	$v_return['status'] = 1;
}
?>
