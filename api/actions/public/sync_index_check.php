<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");
if(!function_exists("fn_create_sync_index")) include(__DIR__."/../../include/fn_create_sync_index.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_sync_index_time = strtotime($v_data["sync_index_time"]);

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$v_row = array();
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_sync_index ORDER BY id DESC LIMIT 1");
	if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
	$l_sync_index_time_local = strtotime($v_row['created']);
	$l_expire_time = time() - (10 * 60); // 10 minutes in past
	
	if($l_sync_index_time_local < $l_expire_time || $v_row['sync'] == 1)
	{
		fn_create_sync_index($o_main);
		$v_row['content_status'] = 1;
	}
	if($l_sync_index_time == $l_sync_index_time_local && $v_row['content_status'] == 0)
	{
		$v_return['status'] = 1;
		$v_return['message'] = "already_synchronized";
	} else {
		$v_return['status'] = 2;
		while($v_row['content_status'] == 1)
		{
			set_time_limit(30);
			$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_sync_index ORDER BY id DESC LIMIT 1");
			if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
		}
		$v_return['id'] = $v_row['id'];
		$v_return['created'] = $v_row['created'];
		$v_return['structure'] = json_decode($v_row['structure']);
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>