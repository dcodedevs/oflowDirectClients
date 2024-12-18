<?php
$email = $v_data['params']['email'];
$creditor_id = $v_data['params']['creditor_id'];
$check_type_no = $v_data['params']['check_type_no'];

if($email == "david@dcode.no") {
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditor_id)."'";
	$o_query = $o_main->db->query($s_sql);
	$session_exists = $o_query ? $o_query->row_array() : array();
} else {
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($email)."' AND creditor_id = '".$o_main->db->escape_str($creditor_id)."'";
	$o_query = $o_main->db->query($s_sql);
	$session_exists = $o_query ? $o_query->row_array() : array();
}
include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");
$creditor = array();
if($session_exists){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_id));
	$creditor = $o_query ? $o_query->row_array() : array();
	if($creditor['trigger_full_reorder']) {
		$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE creditor_id = ? AND open = 1";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$source_id = 1;
		process_open_cases_for_tabs($creditor['id'], $source_id);

		$s_sql = "UPDATE creditor SET trigger_full_reorder = 0 WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	}
	if($creditor['has_mainclaim_payed'] == 1){
		$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND tab_status = 12 AND open = 1";
		$o_query = $o_main->db->query($s_sql, array($creditor_id));
		$count = $o_query ? $o_query->num_rows() : 0;
		$creditor['has_mainclaim_payed_count'] = $count;
	}
	
	$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_email_sending_days = ($o_query ? $o_query->result_array() : array());
	$creditor['creditor_email_sending_days'] = $creditor_email_sending_days;
	
	$s_sql = "SELECT * FROM creditor_reminder_emails WHERE creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_reminder_emails_array = ($o_query ? $o_query->result_array() : array());
	$creditor_reminder_emails = array();
	foreach($creditor_reminder_emails_array as $creditor_reminder_email_item){
		$creditor_reminder_emails[] = $creditor_reminder_email_item['email'];
	}
	$creditor['creditor_reminder_emails'] = $creditor_reminder_emails;
	
    $s_sql = "SELECT * FROM creditor_collecting_company_chat WHERE creditor_id = ?
    AND IFNULL(read_check,0) = 0 AND IFNULL(message_from_oflow, 0) = 1
    GROUP BY collecting_company_case_id
    ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    $unread_messages_count = ($o_query ? $o_query->num_rows() : 0);

	$creditor['unread_messages_count'] = $unread_messages_count;
	$v_return['status'] = 1;

	if($creditor['check_type_no']){
		$type_no = "";
		$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_type_no.php';
		if (file_exists($hook_file)) {
		include $hook_file;
		if (is_callable($run_hook)) {
			$hook_params = array('creditor_id'=>$creditor['id']);
			$hook_result = $run_hook($hook_params);
			// echo implode("<br/>", $hook_result['type_names']);
			if($hook_result['result']) {
				$type_no = $hook_result['result'];
			}
		}
		}
		if($type_no == ""){
			$creditor['error_getting_type_no'] = true;
		}
		$s_sql = "UPDATE creditor SET check_type_no = 0 WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	}
}
$v_return['creditor'] = $creditor;
?>
