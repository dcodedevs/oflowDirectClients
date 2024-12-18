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
	$perPage =  $filters_new['perPage'] ? $filters_new['perPage'] : 200;
	$page =  $filters_new['page'] ? $filters_new['page'] : 1;

	$limit = "";
	if($page > 0){
		$limit = " LIMIT ".$perPage." OFFSET ".($page-1)*$perPage;
	}

	$customer_filter = $filters_new['customer_filter'] ? $filters_new['customer_filter'] : 0;
	$creditor_filter = $filters_new['creditor_filter'] ? $filters_new['creditor_filter'] : 0;
	$list_filter = $filters_new['list_filter'] ? $filters_new['list_filter'] : 0;
	$search_filter = $filters_new['search_filter'] ? $filters_new['search_filter'] : 0;
	$count_only = $filters_new['count_only'];

	if($creditor_filter > 0) {
		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_filter));
		$creditor = ($o_query ? $o_query->row_array() : array());
	} else {
		$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
		$o_query = $o_main->db->query($s_sql, array($customer_filter));
		$creditor = ($o_query ? $o_query->row_array() : array());
	}
	if($creditor) {
		$search_sql = "";
		if(!$count_only){
			if($search_filter != ""){
				$search_sql = " AND customer.name LIKE '%".$o_main->db->escape_str($search_filter)."%'";
			}
		}
		$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND content_status < 2 AND (IFNULL(paStreet, '') = '' OR IFNULL(paPostalNumber, '') = '' OR IFNULL(paCity, '') = '')";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$missing_address_count = ($o_query ? $o_query->num_rows() : 0);
		if(!$count_only){
			$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND content_status < 2";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			$all_customer_count = ($o_query ? $o_query->num_rows() : 0);
			if($list_filter == "all") {
				$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND content_status < 2 ".$search_sql." ORDER BY name ASC";
				$o_query = $o_main->db->query($s_sql.$limit, array($creditor['id']));
				$customers = ($o_query ? $o_query->result_array() : array());
				$o_query = $o_main->db->query($s_sql, array($creditor['id']));
				$filtered_count = ($o_query ? $o_query->num_rows() : 0);
				if($search_filter != ""){
					$totalPages = ceil($filtered_count/$perPage);
				} else {
					$totalPages = ceil($all_customer_count/$perPage);
				}
			} else {
				$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND content_status < 2 ".$search_sql." AND ((paStreet = '' OR paStreet is null) OR (paPostalNumber = '' OR paPostalNumber is null) OR (paCity = '' OR paCity is null)) ORDER BY name ASC";
				$o_query = $o_main->db->query($s_sql.$limit, array($creditor['id']));
				$customers = ($o_query ? $o_query->result_array() : array());
				$o_query = $o_main->db->query($s_sql, array($creditor['id']));
				$filtered_count = ($o_query ? $o_query->num_rows() : 0);
				if($search_filter != ""){
					$totalPages = ceil($filtered_count/$perPage);
				} else {
					$totalPages = ceil($all_customer_count/$perPage);
				}
			}


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


			$v_return['creditor_reminder_custom_profiles'] = $creditor_reminder_custom_profiles;
			$v_return['creditor_profile_for_person'] = $creditor_profile_for_person;
			$v_return['creditor_profile_for_company'] = $creditor_profile_for_company;
			$v_return['creditor_move_to_collecting'] = $creditor_move_to_collecting;
			$v_return['creditor_progress_of_reminder_process'] = $creditor_progress_of_reminder_process;
			$v_return['customer_reminder_profile'] = $customer_reminder_profile;
			$v_return['customer_move_to_collecting'] = $customer_move_to_collecting;
			$v_return['customer_progress_of_reminder_process'] = $customer_progress_of_reminder_process;
			$v_return['customers'] = $customers;
			$v_return['totalPages'] = $totalPages;
			$v_return['all_customer_count'] = $all_customer_count;
			$v_return['filtered_count'] = $filtered_count;
		}

		$v_return['creditor'] = $creditor;
		$v_return['missing_address_count'] = $missing_address_count;

		$v_return['status'] = 1;
	}
}
?>
