<?php
$creditor_id = $v_data['params']['creditor_id'];
$customer_id = $v_data['params']['customer_id'];
if($creditor_id > 0 && $customer_id > 0) {
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
	if($creditor){
		$s_sql = "SELECT * FROM customer WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($customer_id));
		$customer = ($o_query ? $o_query->row_array() : array());

		$v_return['creditor'] = $creditor;
		$v_return['customer'] = $customer;
		$v_return['status'] = 1;
	}

} else {
	$filters_new = $v_data['params']['filters'];

	$customer_filter = $filters_new['customer_filter'] ? $filters_new['customer_filter'] : 0;
	$creditor_filter = $filters_new['creditor_filter'] ? $filters_new['creditor_filter'] : 0;
	$customer_id = $filters_new['customer_id'] ? $filters_new['customer_id'] : 0;
	$list_filter = $filters_new['list_filter'] ? $filters_new['list_filter'] : 0;

	if($creditor_filter > 0) {
		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_filter));
		$creditor = ($o_query ? $o_query->row_array() : array());
	} else {
		$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
		$o_query = $o_main->db->query($s_sql, array($customer_filter));
		$creditor = ($o_query ? $o_query->row_array() : array());
	}
	if($creditor && $customer_id > 0) {
		$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND id = '".$o_main->db->escape_str($customer_id)."'";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$customer = ($o_query ? $o_query->row_array() : array());
		
		$customer_reminder_overviews = array();
		$s_sql = "SELECT * FROM creditor_debitor_reminder_overview_report WHERE creditor_id = ? AND debitor_id = ? ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($creditor['id'], $customer['id']));
		$nonprocessed_customer_reminder_overviews = ($o_query ? $o_query->result_array() : array());
		foreach($nonprocessed_customer_reminder_overviews as $nonprocessed_customer_reminder_overview) {			
			$s_sql = "SELECT * FROM creditor_debitor_reminder_overview_report_sendings WHERE creditor_debitor_reminder_overview_report_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($nonprocessed_customer_reminder_overview['id']));
			$sendings = ($o_query ? $o_query->result_array() : array());
			$nonprocessed_customer_reminder_overview['sendings'] = $sendings;
			$customer_reminder_overviews[] = $nonprocessed_customer_reminder_overview;
		}
		// $s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND id = '".$o_main->db->escape_str($customer_id)."'";
		// $o_query = $o_main->db->query($s_sql, array($creditor['id']));
		// $cases = ($o_query ? $o_query->result_array() : array());

		
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
		$v_return['customer'] = $customer;
		$v_return['creditor_reminder_custom_profiles'] = $creditor_reminder_custom_profiles;

		$s_sql_reminder = "SELECT cc.*, ct.open, ct.case_balance, ct.invoice_nr, ct.collecting_company_case_id FROM collecting_cases cc
		JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
		WHERE cc.creditor_id = ? AND cc.debitor_id = ?";
		$o_query = $o_main->db->query($s_sql_reminder, array($creditor['id'], $customer['id']));
		$reminderCasesCount = ($o_query ? $o_query->num_rows() : 0);

		$s_sql_collecting = "SELECT ccc.*, ct.open, ct.case_balance, GROUP_CONCAT(ct.invoice_nr) as invoice_nrs FROM collecting_company_cases ccc
		JOIN creditor_transactions ct ON ct.collecting_company_case_id = ccc.id
		WHERE ccc.creditor_id = ? AND ccc.debitor_id = ? GROUP BY ccc.id";
		$o_query = $o_main->db->query($s_sql_collecting, array($creditor['id'], $customer['id']));
		$collectingCasesCount = ($o_query ? $o_query->num_rows() : 0);


		if($list_filter == "reminder_cases") {
			$s_sql = $s_sql_reminder;
			$o_query = $o_main->db->query($s_sql, array($creditor['id'], $customer['id']));
			$reminderCases = ($o_query ? $o_query->result_array() : array());
		} else if($list_filter == "collecting_cases") {
			$s_sql = $s_sql_collecting;
			$o_query = $o_main->db->query($s_sql, array($creditor['id'], $customer['id']));
			$collectingCases = ($o_query ? $o_query->result_array() : array());
		}
		$v_return['list_filter'] = $list_filter;
		$v_return['reminder_cases'] = $reminderCases;
		$v_return['collecting_cases'] = $collectingCases;
		$v_return['reminder_cases_count'] = $reminderCasesCount;
		$v_return['collecting_cases_count'] = $collectingCasesCount;
		$v_return['customer_reminder_overviews'] = $customer_reminder_overviews;
		$v_return['status'] = 1;
	}
}
?>
