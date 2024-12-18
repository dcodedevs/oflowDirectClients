<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_folder_id = $v_data['folder_id'];
$s_file_name = trim(str_replace("/", "", $v_data['filename']));

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE id = ?", array($l_folder_id));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_folder = $o_query->row_array();
		if($v_folder['disallow_store_items'] != 1 && $v_folder['device_disallow_store_items'] != 1)
		{
			if($s_file_name != "")
			{
				$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND name = ?", array($l_folder_id, $s_file_name));
				if($o_query && $o_query->num_rows()==0)
				{
					$v_module = array();
					$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'FilearchiveGBox'");
					if($o_query && $o_query->num_rows()>0) $v_module = $o_query->row_array();
					
					$l_sortnr = 1;
					$o_query = $o_main->db->query("SELECT MAX(sortnr) as sortnr FROM sys_filearchive_file WHERE folder_id = ?", array($l_folder_id));
					if($o_query && $o_query->num_rows()>0)
					{
						$v_row = $o_query->row_array();
						$l_sortnr = $v_row['sortnr'] + 1;
					}
					$o_main->db->query("INSERT INTO sys_filearchive_file SET moduleID = ?, createdBy = ?, created = NOW(), sortnr = ?, folder_id = ?, name = ?, content_status = 10", array($v_module['uniqueID'], "GBox_device - ".$l_getynetbox_id, $l_sortnr, $l_folder_id, $s_file_name));
					$l_file_id = $o_main->db->insert_id();
					
					$o_main->db->query("INSERT INTO sys_filearchive_file_version SET moduleID = ?, createdBy = ?, created = NOW(), file_id = ?, name = ?, file = '', status = 0, previous_version = 0, checksum = ''", array($v_module['uniqueID'], "GBox_device - ".$l_getynetbox_id, $l_file_id, $s_file_name));
					$l_file_version_id = $o_main->db->insert_id();
					
					$v_return['status'] = 1;
					$v_return['file_id'] = $l_file_id;
					$v_return['file_version_id'] = $l_file_version_id;
				} else {
					$v_return['status'] = 2;
					$v_return['message'] = "filename_in_use";
				}
			} else {
				$v_return['status'] = 2;
				$v_return['message'] = "filename_cannot_be_empty";
			}
		} else {
			$v_return['status'] = 2;
			$v_return['message'] = "folder_settings_disallow_store_items";
		}
	} else {
		$v_return['status'] = 2;
		$v_return['message'] = "folder_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}

if(isset($v_data['test'])) {
	print_r(json_encode($v_return));
}
?>