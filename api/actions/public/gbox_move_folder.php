<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_folder_id = intval($v_data['folder_id']);
$l_parent_folder_id = intval($v_data['parent_folder_id']);

$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
$v_response = json_decode($s_response,true);

if($v_response['status'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE id = ?", array($l_folder_id));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_folder = $o_query->row_array();
		if($v_folder['disallow_move'] != 1 && $v_folder['device_disallow_move'] != 1)
		{
			if($v_folder['parent_id'] != $l_parent_folder_id)
			{
				$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE id = ?", array($l_parent_folder_id));
				if($l_parent_folder_id == 0 || ($o_query && $o_query->num_rows()>0))
				{
					$v_folder = $o_query->row_array();
					if($v_folder['disallow_store_items'] != 1 && $v_folder['device_disallow_store_items'] != 1)
					{
						$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_folder WHERE parent_id = ? AND name = ?", array($l_parent_folder_id, $v_folder['name']));
						if($o_query && $o_query->num_rows()==0)
						{
							$o_main->db->query("UPDATE sys_filearchive_folder SET parent_id = ? WHERE id = ?", array($l_parent_folder_id, $l_folder_id));
							$o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
							$v_return['status'] = 1;
							$v_return['message'] = "folder_moved";
						} else {
							$v_return['status'] = 2;
							$v_return['message'] = "folder_with_same_name_exits_in_folder";
						}
					} else {
						$v_return['message'] = "folder_settings_disallow_store_items";
					}
				} else {
					$v_return['message'] = "parent_folder_not_found";
				}
			} else {
				$v_return['message'] = "moving_to_same_place";
			}
		} else {
			$v_return['message'] = "folder_settings_disallow_this_action";
		}
	} else {
		$v_return['message'] = "folder_not_found";
	}
} else {
	$v_return['message'] = $v_response['message'];
}
?>