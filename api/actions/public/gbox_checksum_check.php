<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_folder_id = $v_data['folder_id'];
$l_file_version_id = $v_data['file_version_id'];
$s_checksum = $v_data['checksum'];

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE id = ?", array($l_file_version_id));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_row = $o_query->row_array();
		$v_file = json_decode($v_row['file'], true);
		$s_file = __DIR__."/../../../".$v_file[0][1][0];
		if($v_row['checksum'] == $s_checksum)
		{
			$v_return['status'] = 1;
		} else {
			$v_return['message'] = "incorrect_checksum";
		}
	} else {
		$v_return['message'] = "file_version_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>