<?php
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../includes/APIconnect.php");

$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
$o_query = $o_main->db->query($s_sql);
$fw_session = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();

if(isset($fw_session['content_server_api_url']) && '' != $fw_session['content_server_api_url'])
{
	$s_response = APIconnectAccount("account_authenticate", $o_main->accountinfo['accountname'], $o_main->accountinfo['password'], array('VALID_COUNT'=>5000));
	$v_auth = json_decode($s_response, TRUE);
}

$s_sql = 'SELECT id, '.$o_main->db_escape_name($deleteFieldField).' FROM '.$o_main->db_escape_name($deleteFieldTable).' WHERE '.$o_main->db_escape_name($deleteFieldRelID).' = ?';
$o_query = $o_main->db->query($s_sql, array($deleteFieldID));
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result() as $o_row)
	{
		if(isset($fw_session['content_server_api_url']) && '' != $fw_session['content_server_api_url'])
		{
			// Delete on CDN server
			$v_upload = array(
				'items' => array()
			);
			$jsondata = json_decode($o_row->$deleteFieldField, TRUE);
			foreach($jsondata as $obj)
			{
				$v_file_item = array(
					'action' => 'delete',
					'filename' => $obj[0],
					'items' => $obj[1],
					'labels' => $obj[2],
					'links' => $obj[3],
					'upload_id' => $obj[4],
					'focus' => $obj[5]
				);
				$v_upload['items'][] = $v_file_item;
			}
			
			$v_upload['data'] = json_encode(array('action'=>'handle_image'));
			$v_upload['items'] = json_encode($v_upload['items']);
			$v_upload['username'] = $_COOKIE['username'];
			$v_upload['accountname'] = $o_main->accountinfo['accountname'];
			$v_upload['token'] = $v_auth['token'];
			
			//call api
			$ch = curl_init($fw_session['content_server_api_url']);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $v_upload);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$s_response = curl_exec($ch);
			
			$v_file_items = array();
			if($s_response !== false && $s_response != "")
			{
				$v_response = json_decode($s_response, true);
				if(isset($v_response['status']) && 1 == $v_response['status'])
				{
					$v_file_items = $v_response['items'];
				}
				if(isset($v_response['errors']) && 0 < sizeof($v_response['errors']))
				{
					foreach($v_response['errors'] as $s_error) $error_msg["error_".count($error_msg)] = $s_error;
				}
			} else {
				$error_msg["error_".count($error_msg)] = "Error occurred handling request";
			}
		} else {
			// Delete local files
			$jsondata = json_decode($o_row->$deleteFieldField,true);
			foreach($jsondata as $obj)
			{
				$delete_file_fail = false;
				$delete_file = $obj[1];
				foreach($delete_file as $delete_item)
				{
					$delete_item = rawurldecode($delete_item);
					if(is_file(BASEPATH.$delete_item))
					{
						$uploads_dir = "uploads/";
						if(strpos($delete_item,"uploads/protected/")!==false) $uploads_dir = "uploads/protected/";
						if(strpos($delete_item,"uploads/storage/")!==false) $uploads_dir = "uploads/storage/";
						$remove_path = str_replace(BASEPATH.$uploads_dir,"",dirname(BASEPATH.$delete_item));
						
						unlink(BASEPATH.$delete_item);
						if(is_file(BASEPATH.$delete_item))
						{
							$delete_file_fail = true;
						} else {
							// remove directory
							$remove_path = explode("/",$remove_path);
							while(count($remove_path)>0)
							{
								$remove_dir = array_pop($remove_path);
								rmdir(BASEPATH.$uploads_dir.implode("/",$remove_path)."/".$remove_dir);
							}
						}
					}
				}
				if($delete_file_fail)
				{
					$error_msg["error_".count($error_msg)] = "Following file was not deleted for this content or sub-content: ".$obj[0];
				}
			}
		}
	}
}