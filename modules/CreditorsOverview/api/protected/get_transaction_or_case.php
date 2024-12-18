<?php

$transaction_id = $v_data['params']['transaction_id'];
$case_id = $v_data['params']['case_id'];
$customer_id = $v_data['params']['customer_id'];

if($transaction_id > 0){
	$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($transaction_id));
	$transaction = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
	$creditor = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND creditor_customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id'], $transaction['external_customer_id']));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();

} else if($case_id > 0) {
	$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($case_id));
	$collecting_case = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
	$creditor = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($collecting_case['debitor_id']));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();

}
if($customer_id > 0) {
	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($debitorCustomer['creditor_id']));
	$creditor = $o_query ? $o_query->row_array() : array();
}

$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings WHERE content_status < 2";
$o_query = $o_main->db->query($s_sql);
$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT collecting_cases_process.* FROM collecting_cases_process WHERE collecting_cases_process.id = ? ";
$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_person']));
$light_edition_reminder_process_person = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT collecting_cases_process.* FROM collecting_cases_process WHERE collecting_cases_process.id = ? ";
$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_company']));
$light_edition_reminder_process_company = ($o_query ? $o_query->row_array() : array());

$creditor_profile_for_person = $creditor['creditor_reminder_default_profile_id'];
$creditor_profile_for_company = $creditor['creditor_reminder_default_profile_for_company_id'];

$creditor_move_to_collecting = $creditor['choose_move_to_collecting_process'];
$creditor_progress_of_reminder_process = $creditor['choose_progress_of_reminderprocess'];

$customer_reminder_profile = $debitorCustomer['creditor_reminder_profile_id'];
$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];

$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName,
IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name,
IF(ccp.available_for = 1, 1, 0) as isPersonType
FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.creditor_id = ? AND crcp.content_status < 2";
$o_query = $o_main->db->query($s_sql, array($creditor['id']));
$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());


$v_return['creditor'] = $creditor;
$v_return['debitorCustomer'] = $debitorCustomer;
$v_return['collecting_case'] = $collecting_case;
$v_return['transaction'] = $transaction;
$v_return['creditor_reminder_custom_profiles'] = $creditor_reminder_custom_profiles;
$v_return['creditor_profile_for_person'] = $creditor_profile_for_person;
$v_return['creditor_profile_for_company'] = $creditor_profile_for_company;
$v_return['creditor_move_to_collecting'] = $creditor_move_to_collecting;
$v_return['creditor_progress_of_reminder_process'] = $creditor_progress_of_reminder_process;
$v_return['customer_reminder_profile'] = $customer_reminder_profile;
$v_return['customer_move_to_collecting'] = $customer_move_to_collecting;
$v_return['customer_progress_of_reminder_process'] = $customer_progress_of_reminder_process;
$v_return['collecting_system_settings'] = $collecting_system_settings;
$v_return['light_edition_reminder_process_person'] = $light_edition_reminder_process_person;
$v_return['light_edition_reminder_process_company'] = $light_edition_reminder_process_company;
$v_return['status'] = 1;
?>
