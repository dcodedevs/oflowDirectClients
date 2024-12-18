<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_folder_id = $v_data['folder_id'];

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

// Currently not allowed to delte folders
if(1==0 && $v_response['status'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE id = ?", array($l_folder_id));
	if($o_query && $o_query->num_rows()>0)
	{
		$o_main->db->query("UPDATE sys_filearchive_folder SET content_status = 2 WHERE id = ?", array($l_folder_id));
		$o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
		$v_return['status'] = 1;
		$v_return['message'] = "folder_deleted";
	} else {
		$v_return['message'] = "folder_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>