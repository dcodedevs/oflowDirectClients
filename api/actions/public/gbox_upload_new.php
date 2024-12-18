<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");

$s_token = $v_data["token"];
$l_getynetbox_id = $v_data["getynetboxid"];
$l_account_id = $v_data["accountid"];
$l_folder_id = $v_data['folder_id'];
$l_file_version_id = $v_data['file_version_id'];
$s_checksum = $v_data["checksum"];

if($_FILES["file"]["error"] == UPLOAD_ERR_OK)
{
	$s_response = APIconnectOpen("getynetboxaccesscheck", array("TOKEN"=>$s_token, "ACCOUNTID"=>$l_account_id, "GETYNETBOXID"=>$l_getynetbox_id));
	$v_response = json_decode($s_response,true);
	
	if($v_response['status'] == 1)
	{
		$o_query = $o_main->db->query("SELECT * FROM sys_filearchive_file_version WHERE id = ? AND status = 0", array($l_file_version_id));
		if($o_query && $o_query->num_rows()>0)
		{
			$v_row = $o_query->row_array();
			$l_file_id = $v_row['file_id'];
			$s_file_name = $v_row['name'];
			
			$o_main->db->query("INSERT INTO uploads SET createdBy = ?, created = NOW(), filename = ?, filepath = '', size = 0, handle_status = 0, fileupload_session_id = ?", array("GBox_device - ".$l_getynetbox_id, $s_file_name, $s_token));
			$l_uploads_id = $o_main->db->insert_id();
			
			$s_account_path = realpath(__DIR__."/../../..");
			$s_file_path = "uploads/protected/".$l_uploads_id."/0/".$s_file_name;
			$s_file_path_storage = "uploads/storage/".$l_uploads_id."/0/".$s_file_name;
			
			mkdir(dirname($s_account_path."/".$s_file_path),octdec(2777),true);
			move_uploaded_file($_FILES["file"]["tmp_name"], $s_account_path."/".$s_file_path);
			mkdir(dirname($s_account_path."/".$s_file_path_storage),octdec(2777),true);
			move_uploaded_file($_FILES["file"]["tmp_name"], $s_account_path."/".$s_file_path_storage);
			
			$v_file = array(
				'0' => $s_file_name,
				'1' => array($s_file_path),
				'2' => array(),
				'3' => '',
				'4' => $l_uploads_id
			);
			$v_files = array($v_file);
			
			if(is_file($s_account_path."/".$s_file_path))
			{
				$o_main->db->query("UPDATE uploads SET filepath = ? WHERE id = ?", array($s_file_path_storage, $l_uploads_id));
			}
			
			if(is_file($s_account_path."/".$s_file_path))
			{
				$s_hash = sha1_file($s_account_path."/".$s_file_path);
				if(strtolower($s_hash) == strtolower($s_checksum))
				{
					$v_return['status'] = 1;
					$v_return['message'] = "file_uploaded";
					$v_return['file_id'] = $l_file_id;
					
					$o_main->db->query("UPDATE sys_filearchive_file SET content_status = 0 WHERE id = ?", array($l_file_id));
					$o_main->db->query("UPDATE sys_filearchive_file_version SET status = 1, file = ?, checksum = ? WHERE id = ?", array(json_encode($v_files), $s_hash, $l_file_version_id));
					$o_main->db->query("UPDATE uploads SET handle_status = 1 WHERE id = ?", array($l_uploads_id));
					$o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
				} else {
					$v_return['message'] = "checksum_issue ===" . $s_hash . "===" . $s_checksum;
				}
			} else {
				$v_return['message'] = "error_occured_saving_file";
			}
			
			if($v_return['status'] != 1)
			{
				$o_main->db->query("DELETE FROM sys_filearchive_file WHERE id = ?", array($l_file_id));
				$o_main->db->query("DELETE FROM sys_filearchive_file_version WHERE id = ?", array($l_file_version_id));
			}
		} else {
			$v_return['message'] = "file_version_not_found";
		}
	} else {
		$v_return['message'] = $v_response['message'];
	}
} else {
	$v_return['message'] = "error_with_uploaded_file";
}
?>