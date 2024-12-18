<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_file_id = $v_data['file_id'];
$s_filename = $v_data["filename"];

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file WHERE id = ?", array($l_file_id));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_file = $o_query->row_array();
		if($v_file['disallow_rename'] != 1 && $v_file['device_disallow_rename'] != 1)
		{
			$v_module = array();
			$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'FilearchiveGBox'");
			if($o_query && $o_query->num_rows()>0) $v_module = $o_query->row_array();
			
			$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 0", array($v_file['id']));
			if($o_query && $o_query->num_rows()==0)
			{
				$o_main->db->query("INSERT INTO sys_filearchive_file_version SET moduleID = ?, createdBy = ?, created = NOW(), file_id = ?, name = ?, file = '', status = 0, previous_version = 0, checksum = ''", array($v_module['uniqueID'], "GBox_device - ".$l_getynetbox_id, $v_file['id'], $s_filename));
				$l_file_version_id = $o_main->db->insert_id();
				
				$v_file_version = array();
				$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC", array($v_file['id']));
				if($o_query && $o_query->num_rows()>0) $v_file_version = $o_query->row_array();
				$v_files = json_decode($v_file_version['file'], true);
				$v_files[0][0] = $s_filename;
				$s_account_path = realpath(__DIR__."/../../..");
				$v_tmp = explode("/", $v_files[0][1][0]);
				$l_last = count($v_tmp)-1;
				$v_tmp[$l_last] = $s_filename;
				$s_new_file_path = implode("/", $v_tmp);
				rename($s_account_path."/".$v_files[0][1][0], $s_account_path."/".$s_new_file_path);
				$v_files[0][1][0] = $s_new_file_path;
				$s_files = json_encode($v_files);
				if(is_file($s_account_path."/".$s_new_file_path))
				{
					$o_main->db->query("UPDATE sys_filearchive_file_version SET status = 1, file = ?, previous_version = ?, checksum = ? WHERE id = ?", array($s_files, $v_file_version['id'], $v_file_version['checksum'], $l_file_version_id));
					
					$o_main->db->query("UPDATE sys_filearchive_file SET name = ? WHERE id = ?", array($s_filename, $v_file['id']));
					$o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
					
					$v_return['status'] = 1;
					$v_return['message'] = "file_renamed";
					$v_return['file_version_id'] = $l_file_version_id;
				} else {
					$o_main->db->query("DELETE FROM sys_filearchive_file_version WHERE id = ?", array($l_file_version_id));
					$v_return['message'] = "error_occured_renaming_file";
				}
			} else {
				$v_return['message'] = "other_instance_conflict";
			}
		} else {
			$v_return['message'] = "file_settings_disallow_this_action";
		}
	} else {
		$v_return['message'] = "file_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>