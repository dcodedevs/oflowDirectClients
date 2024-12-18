<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_file_id = $v_data['file_id'];
$s_checksum = $v_data["checksum"];

if($_FILES["file"]["error"] == UPLOAD_ERR_OK)
{
	$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
	$v_response = json_decode($s_response,true);
	
	if($v_response['status'] == 1)
	{
		$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file WHERE id = ?", array($l_file_id));
		if($o_query && $o_query->num_rows()>0)
		{
			$v_file = $o_query->row_array();
			$v_module = array();
			$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'FilearchiveGBox'");
			if($o_query && $o_query->num_rows()>0) $v_module = $o_query->row_array();
			
			$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 0", array($$v_file['id']));
			if($o_query && $o_query->num_rows()==0)
			{
				$o_main->db->query("INSERT INTO sys_filearchive_file_version SET moduleID = ?, createdBy = ?, created = NOW(), file_id = ?, name = ?, file = '', status = 0, previous_version = '', checksum = ''", array($v_module['uniqueID'], "GBox_device - ".$l_getynetbox_id, $v_file['id'], $v_file['name']));
				$l_file_version_id = $o_main->db->insert_id();
				
				$v_file_version = array();
				$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC", array($v_file['id']));
				if($o_query && $o_query->num_rows()>0) $v_file_version = $o_query->row_array();
				$v_files = json_decode($v_file_version['file'], true);
				$s_account_path = realpath(__DIR__."/../../..");
				$s_file_path = $v_files[0][1][0];
				
				move_uploaded_file($_FILES["file"]["tmp_name"], $s_account_path."/".$s_file_path."_");
				
				if(is_file($s_account_path."/".$s_file_path."_"))
				{
					$s_hash = sha1_file($s_account_path."/".$s_file_path."_");
					if(strtolower($s_hash) == strtolower($s_checksum))
					{
						unlink($s_account_path."/".$s_file_path);
						rename($s_account_path."/".$s_file_path."_", $s_account_path."/".$s_file_path);
						$v_return['status'] = 1;
						$v_return['message'] = "file_uploaded";
						
						$o_main->db->query("UPDATE sys_filearchive_file_version SET status = 1, file = ?, previous_version = ?, checksum = ? WHERE id = ?", array($v_file_version['file'], $v_file_version['id'], $s_hash, $l_file_version_id));
						$o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
					} else {
						$v_return['message'] = "checksum_issue";
						unlink($s_account_path."/".$s_file_path."_");
					}
				} else {
					$v_return['message'] = "error_occured_saving_file";
				}
				
				if($v_return['status'] != 1)
				{
					$o_main->db->query("DELETE FROM sys_filearchive_file_version WHERE id = '".$l_file_version_id."'");
				}
			} else {
				$v_return['message'] = "other_instance_conflict";
			}
		} else {
			$v_return['message'] = "file_not_found";
		}
	} else {
		$v_return['message'] = $v_response['message'];
	}
} else {
	$v_return['message'] = "error_with_uploaded_file";
}
?>