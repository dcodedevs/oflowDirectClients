<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_parent_folder_id = intval($v_data['parent_folder_id']);
$s_foldername = trim(str_replace("/","",$v_data["foldername"]));

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	if(strlen($s_foldername)>0)
	{
		$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE id = ?", array($l_parent_folder_id));
		if($l_parent_folder_id==0 || ($o_query && $o_query->num_rows()>0))
		{
			$v_folder = $o_query->row_array();
			if($l_parent_folder_id==0 || ($v_folder['disallow_store_items'] != 1 && $v_folder['device_disallow_store_items'] != 1))
			{
				$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE parent_id = ? AND name = ?", array($l_parent_folder_id, $s_foldername));
				if($o_query && $o_query->num_rows()==0)
				{
					$v_module = array();
					$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'FilearchiveGBox'");
					if($o_query && $o_query->num_rows()>0) $v_module = $o_query->row_array();
					
					$o_main->db->query("INSERT INTO sys_filearchive_folder (id, moduleID, createdBy, created, parent_id, name) VALUES (NULL, ?, ?, NOW(), ?, ?)", array($v_module['uniqueID'], "GBox_device - ".$l_getynetbox_id, $l_parent_folder_id, $s_foldername));
					$l_folder_id = $o_main->db->insert_id();
					$o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
					
					$v_return['status'] = 1;
					$v_return['message'] = "folder_created";
					$v_return['folder_id'] = $l_folder_id;
				} else {
					$v_return['message'] = "folder_with_same_name_exists";
				}
			} else {
				$v_return['message'] = "folder_settings_disallow_store_items";
			}
		} else {
			$v_return['message'] = "parent_folder_not_found";
		}
	} else {
		$v_return['message'] = "empty_name";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>