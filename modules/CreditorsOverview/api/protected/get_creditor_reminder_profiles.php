<?php
$filters_new = $v_data['params']['filters'];

$customer_filter = $filters_new['customer_filter'] ? $filters_new['customer_filter'] : 0;
$creditor_filter = $filters_new['creditor_filter'] ? $filters_new['creditor_filter'] : 0;

if($creditor_filter > 0){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$creditor = ($o_query ? $o_query->row_array() : array());
} else {
	$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_filter));
	$creditor = ($o_query ? $o_query->row_array() : array());
}
if($creditor){
	$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE creditor_id = ? AND content_status < 2";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());
	$processed_profiles = array();
	foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
		$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['id']));
		$profile_values = ($o_query ? $o_query->result_array() : array());
		$processed_profile_values = array();
		foreach($profile_values as $profile_value){
			$s_sql = "SELECT * FROM creditor_reminder_custom_profile_value_fees WHERE creditor_reminder_custom_profile_value_id = ? ORDER BY mainclaim_from_amount ASC";
			$o_query = $o_main->db->query($s_sql, array($profile_value['id']));
			$fees = ($o_query ? $o_query->result_array() : array());
			$profile_value['fees'] = $fees;
			$processed_profile_values[$profile_value['collecting_cases_process_step_id']] = $profile_value;
		}

		$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
		$default_process = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, array($default_process['id']));
		$old_steps = ($o_query ? $o_query->result_array() : array());
		$steps = array();
		foreach($old_steps as $old_step) {
			$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
			$o_query = $o_main->db->query($s_sql, array($old_step['id']));
			$fees = ($o_query ? $o_query->result_array() : array());
			$old_step['fees'] = $fees;
			$steps[] = $old_step;
		}
		$default_process['steps'] = $steps;


		$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
	    $o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
	    $currentProcess = $o_query ? $o_query->row_array() : array();
		$isPersonType = 0;
		if($currentProcess['available_for'] == 1){
			$isPersonType = 1;
		}
		$creditor_reminder_custom_profile['profile_values'] = $processed_profile_values;
		$creditor_reminder_custom_profile['process'] = $default_process;
		if($creditor_reminder_custom_profile['name'] == ""){
			$creditor_reminder_custom_profile['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
		}
		$creditor_reminder_custom_profile['isPersonType'] = $isPersonType;
		$processed_profiles[] = $creditor_reminder_custom_profile;
	}

	$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$pdfTexts = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$emailTexts = $o_query ? $o_query->result_array() : array();


	$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings WHERE content_status < 2";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT collecting_cases_process.*, pst.name as stepTypeName FROM collecting_cases_process LEFT JOIN process_step_types pst ON pst.id = collecting_cases_process.process_step_type_id WHERE collecting_cases_process.id = ? ";
	$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_person']));
	$light_edition_reminder_process_person = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($light_edition_reminder_process_person['id']));
	$steps = ($o_query ? $o_query->result_array() : array());
	$light_edition_reminder_process_person['steps'] = $steps;

	$s_sql = "SELECT collecting_cases_process.*, pst.name as stepTypeName FROM collecting_cases_process LEFT JOIN process_step_types pst ON pst.id = collecting_cases_process.process_step_type_id WHERE collecting_cases_process.id = ? ";
	$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_company']));
	$light_edition_reminder_process_company = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($light_edition_reminder_process_company['id']));
	$steps = ($o_query ? $o_query->result_array() : array());
	$light_edition_reminder_process_company['steps'] = $steps;

	$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_email_sending_days = ($o_query ? $o_query->result_array() : array());

	$v_return['creditor'] = $creditor;
	$v_return['creditor_email_sending_days'] = $creditor_email_sending_days;
	$v_return['creditor_reminder_custom_profiles'] = $processed_profiles;
	$v_return['emailTexts'] = $emailTexts;
	$v_return['pdfTexts'] = $pdfTexts;
	$v_return['status'] = 1;
	$v_return['collecting_system_settings'] = $collecting_system_settings;
	$v_return['light_edition_reminder_process_person'] = $light_edition_reminder_process_person;
	$v_return['light_edition_reminder_process_company'] = $light_edition_reminder_process_company;
}
?>
