<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");
if(!function_exists("fn_create_sync_index")) include(__DIR__."/../../include/fn_create_sync_index.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$b_create_sync_index = true;
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_sync_index WHERE content_status = 1 AND created < NOW()");
	while($o_query && $o_query->num_rows()>0)
	{
		$b_create_sync_index = false;
		set_time_limit(30);
	}
	if($b_create_sync_index) fn_create_sync_index($o_main);
	
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_sync_index WHERE content_status = 0 ORDER BY id DESC LIMIT 1");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_row = $o_query->row_array();
		$v_return['id'] = $v_row['id'];
		$v_return['created'] = $v_row['created'];
		$v_return['structure'] = json_decode($v_row['structure']);
		$v_return['status'] = 1;
	} else {
		$v_return['message'] = "sync_index_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>