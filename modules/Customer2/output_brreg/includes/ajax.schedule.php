<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$l_type = $_POST['type'];
$l_delay = intval($_POST['delay']);
$o_query = $o_main->db->query("SELECT * FROM customer_sync_data LIMIT 1");
if($o_query && $o_query->num_rows()>0)
{
	$fw_error_msg = array($formText_ApproveFirstAlreadyGeneratedReport_Output);
} else {
//if($l_hour >= 0 && $l_hour <= 23 && $l_minute >= 0 && $l_minute <= 59)
//{
	$s_time = date('YmdHi');
	$b_register_cronjob_task = FALSE;
	$v_param = array(
		'l_type'=>$l_type,
		'l_delay'=>$l_delay
	);
	$s_param = json_encode($v_param);
	$o_query = $o_main->db->query("SELECT * FROM sys_cronjob WHERE content_id = 1 AND script_path = '".$o_main->db->escape_str('modules/Customer2/output_brreg/cron_sync_brreg.php')."'");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_sys_cronjob = $o_query->row_array();
		//if(strtotime($v_sys_cronjob['perform_time']) != $l_time)
		if($v_sys_cronjob['status'] > 1)
		{
			$b_register_cronjob_task = TRUE;
			$l_cronjob_id = $v_sys_cronjob['id'];
			$o_main->db->query("UPDATE sys_cronjob SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($_COOKIE['username'])."', perform_time = STR_TO_DATE('".$o_main->db->escape_str($s_time)."', '%Y%m%d%H%i'), status = 0, repeat_after_seconds = 0, parameters = '".$o_main->db->escape_str($s_param)."' WHERE id = '".$o_main->db->escape_str($l_cronjob_id)."'");
		} else {
			$fw_error_msg = array($formText_SyncProcessAlreadyRunning_Output);
		}
	} else {
		$b_register_cronjob_task = TRUE;
		$o_main->db->query("INSERT INTO sys_cronjob SET created = NOW(), createdBy = '".$o_main->db->escape_str($_COOKIE['username'])."', perform_time = STR_TO_DATE('".$o_main->db->escape_str($s_time)."', '%Y%m%d%H%i'), status = 0, repeat_after_seconds = 0, parameters = '".$o_main->db->escape_str($s_param)."', perform_count = 0, repeat_amount = 0, content_id = 1, script_path = '".$o_main->db->escape_str('modules/Customer2/output_brreg/cron_sync_brreg.php')."'");
		
		$l_cronjob_id = $o_main->db->insert_id();
	}
	
	if($b_register_cronjob_task)
	{
		$o_query = $o_main->db->get('accountinfo');
		$v_accountinfo = $o_query ? $o_query->row_array() : array();
		$s_response = APIconnectAccount('cronjobtaskcreate', $v_accountinfo['accountname'], $v_accountinfo['password'], array('TYPE'=>'script', 'TIME'=>$s_time, 'DATA'=>array('l_cronjob_id'=>$l_cronjob_id)));
		if($s_response != 'OK')
		{
			$fw_error_msg = array($formText_ErrorOccurredHandlingRequest_Output);
		}
	}
/*} else {
	$fw_error_msg = array($formText_ErrorOccurredHandlingRequest_Output);
}*/
}