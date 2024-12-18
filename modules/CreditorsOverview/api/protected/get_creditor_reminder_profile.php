<?php
$creditor_id = $v_data['params']['creditor_id'];
$profile_id = $v_data['params']['profile_id'];


$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE ccp.creditor_id = ? AND ccp.published = 1 ORDER BY ccp.sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$customized_processes_un = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE (ccp.creditor_id = 0 OR ccp.creditor_id is null) AND ccp.published = 1 ORDER BY ccp.sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$default_processes_un = ($o_query ? $o_query->result_array() : array());

	$customized_processes = array();
	foreach($customized_processes_un as $customized_process) {
		$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, array($customized_process['id']));
		$old_steps = ($o_query ? $o_query->result_array() : array());
		$steps = array();
		foreach($old_steps as $old_step) {
			$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
			$o_query = $o_main->db->query($s_sql, array($old_step['id']));
			$fees = ($o_query ? $o_query->result_array() : array());
			$old_step['fees'] = $fees;
			$steps[] = $old_step;
		}
		$customized_process['steps'] = $steps;
		$customized_processes[] = $customized_process;
	}
	$default_processes = array();
	foreach($default_processes_un as $default_process) {
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
		$default_processes[] = $default_process;
	}

	$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, CONCAT_WS(' ', ccp.fee_level_name, pst.name) as name
	FROM creditor_reminder_custom_profiles crcp
	LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE crcp.creditor_id = ?  ORDER BY ccp.sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());
	$processed_profiles = array();
	foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
		$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['id']));
		$old_values = ($o_query ? $o_query->result_array() : array());
		$values = array();
		foreach($old_values as $old_value) {
			$s_sql = "SELECT * FROM creditor_reminder_custom_profile_value_fees WHERE creditor_reminder_custom_profile_value_id = ? ORDER BY mainclaim_from_amount ASC";
			$o_query = $o_main->db->query($s_sql, array($old_value['id']));
			$fees = ($o_query ? $o_query->result_array() : array());
			$old_value['fees'] = $fees;
			$values[] = $old_value;
		}

		$creditor_reminder_custom_profile['values'] = $values;
		$processed_profiles[] = $creditor_reminder_custom_profile;
	}


	$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$pdfTexts = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$emailTexts = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name
	FROM creditor_reminder_custom_profiles crcp
	LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE crcp.id = ?";
	$o_query = $o_main->db->query($s_sql, array($profile_id));
	$profile = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ? AND content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($profile['id']));
	$profile_values = $o_query ? $o_query->result_array() : array();
	$processed_profile_values = array();
	foreach($profile_values as $profile_value){
		$s_sql = "SELECT * FROM creditor_reminder_custom_profile_value_fees WHERE creditor_reminder_custom_profile_value_id = ? ORDER BY mainclaim_from_amount ASC";
		$o_query = $o_main->db->query($s_sql, array($profile_value['id']));
		$fees = ($o_query ? $o_query->result_array() : array());
		$profile_value['fees'] = $fees;

		$processed_profile_values[$profile['reminder_process_id']][$profile_value['collecting_cases_process_step_id']] = $profile_value;
	}

	$s_sql = "SELECT * FROM process_step_types WHERE content_status < 2 ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql);
	$process_step_types = $o_query ? $o_query->result_array() : array();

	$v_return['customized_processes'] = $customized_processes;
	$v_return['default_processes'] = $default_processes;

	$v_return['profile'] = $profile;
	$v_return['profile_values'] = $processed_profile_values;
	$v_return['emailTexts'] = $emailTexts;
	$v_return['pdfTexts'] = $pdfTexts;
	$v_return['process_step_types'] = $process_step_types;

	$v_return['creditor'] = $creditor;
	$v_return['creditor_reminder_custom_profiles'] = $processed_profiles;
	$v_return['status'] = 1;
}
?>
