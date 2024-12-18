<?php

$customer_id = $v_data['params']['customer_id'];
$creditor_id = $v_data['params']['creditor_id'];
$customer_type = $v_data['params']['customer_type'];

$username= $v_data['params']['username'];

$s_sql = "SELECT * FROM customer WHERE id = ? AND creditor_id = ?";
$o_query = $o_main->db->query($s_sql, array($customer_id, $creditor_id));
$customer = ($o_query ? $o_query->row_array() : array());

if($customer) {
	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
	if($creditor) {
        $s_sql = "UPDATE customer SET customer_type_collect_addition = ? WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($customer_type+1, $customer['id']));
        if($o_query) {

			$v_return['status'] = 1;
			// if($creditor['integration_module'] == "Integration24SevenOffice"){
			// 	$hook_params = array(
			// 		'external_customer_id' => $customer['creditor_customer_id'],
			// 		'creditor_id' => $creditor['id'],
			// 		'type'=>$customer_type,
			// 		'username'=> $username
			// 	);
			//
			// 	$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/update_customer_type.php';
			// 	if (file_exists($hook_file)) {
			// 		include $hook_file;
			// 		if (is_callable($run_hook)) {
			// 			$hook_result = $run_hook($hook_params);
			// 			$v_return['hook_result'] = $hook_result;
			// 			if($hook_result['result']){
			// 				$v_return['status'] = 1;
			// 			} else {
			// 				$v_return['error'] = $hook_result['error'];
			// 				// var_dump("deleteError".$hook_result['error']);
			// 			}
			// 		}
			// 	}
			// }
        } else {
            $v_return['error'] = 'Failed';
        }
	}
} else {
    $v_return['error'] = 'No customer info';
}
?>
