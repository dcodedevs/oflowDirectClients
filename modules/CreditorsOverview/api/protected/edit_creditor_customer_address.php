<?php
$_POST = $v_data['params']['post'];
$creditor_id = $_POST['creditor_id'];
$customer_id = $_POST['customer_id'];
$action = $_POST['action'];
$username = $v_data['params']['username'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor) {
	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$customer = ($o_query ? $o_query->row_array() : array());

	if($customer){

		if($creditor['integration_module'] == "Integration24SevenOffice"){
			$hook_params = array(
				'external_customer_id' => $customer['creditor_customer_id'],
				'creditor_id' => $creditor['id'],
				'address'=>$_POST['address'],
				'postalNumber'=>$_POST['postalNumber'],
				'city'=>$_POST['city'],
				'username'=> $username
			);

			$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/update_customer_address.php';
			if (file_exists($hook_file)) {
				include $hook_file;
				if (is_callable($run_hook)) {
					$hook_result = $run_hook($hook_params);
					$v_return['hook_result'] = $hook_result;
					if($hook_result['result']){
						$s_sql = "UPDATE customer SET
						updated = NOW(),
						updatedBy = '".$o_main->db->escape_str($username)."',
						paStreet = '".$o_main->db->escape_str($_POST['address'])."',
						paPostalNumber = '".$o_main->db->escape_str($_POST['postalNumber'])."',
						paCity = '".$o_main->db->escape_str($_POST['city'])."'
						WHERE id = '".$o_main->db->escape_str($customer['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						if($o_query){
							$v_return['status'] = 1;
						}
					} else {
						$v_return['error'] = $hook_result['error'];
						// var_dump("deleteError".$hook_result['error']);
					}
				}
			}
		}
	}

	$v_return['creditor'] = $creditor;
	$v_return['customer'] = $customer;
}
?>
