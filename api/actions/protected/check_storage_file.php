<?php
/*
 * Version 1.3
*/
$file_src = "";
require_once(__DIR__."/../../../fw/account_fw/includes/fn_log_action.php");

if(isset($v_data['file'], $v_data['uid'], $v_data['caID'], $v_data['sessionID'], $v_data['username'], $v_data['ip_address']))
{
	$v_param = array('companyaccessID' => $v_data['caID'], 'session' => $v_data['sessionID'], 'username' => $v_data['username'], 'IP' => $v_data['ip_address']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query && $o_query->num_rows()>0)
	{
		$o_query = $o_main->db->query("SELECT * FROM uploads WHERE id = ?", array($v_data['uid']));
		if($o_query && $o_query->num_rows()>0)
		{
			$content = $o_query->row_array();
			$v_file = explode("/", $v_data['file']);
			$s_file = array_pop($v_file);
			
			if($content['filename'] == $s_file)
			{
				$file_src = $v_data['file'];
			} else if($content['filename'] == rawurlencode($s_file))
			{
				$file_src = implode("/", $v_file)."/".rawurlencode($s_file);
			}
		}
	}
}
if($file_src!="")
{
	log_action("uploads_storage_get");
	$v_return['status'] = 1;
} else {
	log_action("uploads_storage_fail");
}