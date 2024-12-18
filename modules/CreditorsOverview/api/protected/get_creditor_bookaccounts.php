<?php
$creditor_id = $v_data['params']['creditor_id'];
$username = $v_data['params']['username'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$integration_departments = array();
	$integration_projects = array();
	$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_bookaccounts.php';
	if (file_exists($hook_file)) {
		include $hook_file;
		if (is_callable($run_hook)) {
			$hook_params = array('creditor_id'=>$creditor['id'], 'username'=> $username);
		 	$hook_result = $run_hook($hook_params);
			$integration_bookaccounts = $hook_result['bookaccounts'];
		}
	}
	$v_return['creditor'] = $creditor;
	$v_return['integration_bookaccounts'] = $integration_bookaccounts;
	$v_return['status'] = 1;
}
?>
