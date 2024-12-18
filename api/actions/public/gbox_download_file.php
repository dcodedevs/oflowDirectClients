<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_folder_id = $v_data['folder_id'];
$l_file_version_id = $v_data['file_version_id'];

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE id = ? AND status = 1", array($l_file_version_id));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_row = $o_query->row_array();
		$v_file = json_decode($v_row['file'], true);
		$s_file = __DIR__."/../../../".$v_file[0][1][0];
		if(is_file($s_file))
		{
			$filenameArray	= explode("/", $s_file);
			$filename		= $s_file;
			if(count($filenameArray) > 1)
			{
				$filename	= end($filenameArray);
			}
			
			header("Content-Disposition: attachment; filename=".$filename);
			header("Content-Length: " . filesize($s_file));
			header("Content-Type: application/octet-stream;");
			
			readfile($s_file);
			exit;
		} else {
			$v_return['message'] = "file_not_found";
		}
	} else {
		$v_return['message'] = "file_version_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
exit;
?>