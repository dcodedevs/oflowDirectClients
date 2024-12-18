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
	$search = $filters_new['search'] ? $filters_new['search'] : 0;

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
		$sql_where = " AND customer.content_status < 2";
		if($search != "") {
			$sql_where = " AND (customer.name LIKE '%".$o_main->db->escape_like_str($search)."%' OR customer.middlename LIKE '%".$o_main->db->escape_like_str($search)."%' OR customer.lastname LIKE '%".$o_main->db->escape_like_str($search)."%')";
		}
		$s_sql = "SELECT customer.*, COUNT(collecting_cases.id) as caseCount FROM customer
		LEFT OUTER JOIN collecting_cases ON collecting_cases.debitor_id = customer.id
		WHERE customer.creditor_id = ?".$sql_where."
		GROUP BY customer.id";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$customers = ($o_query ? $o_query->result_array() : array());

		$v_return['creditor'] = $creditor;
		$v_return['customers'] = $customers;
		$v_return['status'] = 1;
	}
}
?>
