<?php
$creditor_id = $v_data['params']['creditor_id'];
$username = $v_data['params']['username'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$integration_departments = array();
	$integration_projects = array();
	$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_departments_and_projects.php';
	if (file_exists($hook_file)) {
		include $hook_file;
		if (is_callable($run_hook)) {
			$hook_params = array('creditor_id'=>$creditor['id'], 'username'=> $username);
		 	$hook_result = $run_hook($hook_params);
			$integration_departments = $hook_result['departments'];
			$integration_projects = $hook_result['projects'];
		}
	}
	$v_return['creditor'] = $creditor;
	$v_return['integration_departments'] = $integration_departments;
	$v_return['integration_projects'] = $integration_projects;
	$v_return['status'] = 1;
}
?>
