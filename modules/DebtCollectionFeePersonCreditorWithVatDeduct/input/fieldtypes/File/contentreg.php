<?php
set_time_limit(500);
$account_path = realpath(__DIR__."/../../../../../");
if(!function_exists("dirsizeexec")) include(__DIR__."/fn_dirsizeexec.php");
if(!function_exists("mkdir_recursive")) include(__DIR__."/fn_mkdir_recursive.php");
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../includes/APIconnect.php");
if($fields[$fieldPos][9]==1)
{
	$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
} else {
	$type = ($fields[$fieldPos][11]=="" ? 't1' : strtolower($fields[$fieldPos][11]));
	list($fieldtype, $limit) = explode(',',$type);
	$file_count_limit = ($limit>0?$limit:1);
	
	if(strpos($fieldtype,"s") !== false)
	{
		$output_languages = array("all"=>"");
	}  else {
		$output_languages = array();
		$langName = array();
		$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result() as $o_row)
			{
				$output_languages[$o_row->languageID] = $o_row->name;
			}
		}
	}
	
	if(strpos($fieldtype,"t2") !== false)
	{
		$show_text = true;
	} else {
		$show_text = false;
	}
	if(strpos($fieldtype,"link") !== false)
	{
		$show_link = true;
	} else {
		$show_link = false;
	}
	if(strpos($fieldtype,"p") !== false)
	{
		$protected = true;
	} else {
		$protected = false;
	}
	if(strpos($fieldtype,"o") !== false)
	{
		$b_remove_original = false;
	} else {
		$b_remove_original = true;
	}
	
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	$fw_session = $o_query ? $o_query->row_array() : array();
	
	if(isset($fw_session['content_server_api_url']) && '' != $fw_session['content_server_api_url'])
	{
		// Handle on CDN server
			$v_upload = array(
			'upload_quota' => 0,
			'file_count_limit' => $file_count_limit,
			'items' => array()
		);
		if(array_key_exists($fieldName."_name",$_POST))
		{
			$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
			$o_query = $o_main->db->get_where('session_framework', $v_param);
			$fw_session = $o_query ? $o_query->row_array() : array();
			$v_upload['upload_quota'] = $fw_session['upload_quota'];
			
			foreach($_POST[$fieldName."_name"] as $key => $item)
			{
				$file = array(
					'action'=>'',
					'items'=>array()
				);
				if(strpos($item,"process|") !== false)
				{
					// new uploads or delete old files
					$file_name = explode("|",$item); //process|upload_id|counter|filename
					$file['filename'] = $file_name[3];
					$file['upload_id'] = $file_name[1];
					
					$file_obj = explode("|",$_POST[$fieldName."_file".$file_name[2]]); //process|upload_id|img_src
					if($file_obj[0] == "delete")
					{
						$file['action'] = 'delete';
						$file['items'][] = $file_obj[1];
					} else if($file_obj[0] == "process")
					{
						$file['action'] = 'process';
						$v_properties = array();
						$v_properties['protected'] = $protected;
						$v_properties['path'] = $file_obj[2];
						$file['items'][] = $v_properties;
						
					}
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$file_name[2]];
						}
					}
					$file['labels'] = $x;
					$file['links'] = ($show_link ? $_POST[$fieldName."_link".$file_name[2]] : "");
					$file['remove_original'] = $b_remove_original;
				} else {
					// old files
					$file_name = explode("|",$item); //process|upload_id|counter|filename
					$file['filename'] = $file_name[3];
					$file['upload_id'] = $file_name[1];
					$file['action'] = 'handled_items';
					$file['items'] = array($_POST[$fieldName."_file".$file_name[2]]);
					
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$file_name[2]];
						}
					}
					$file['labels'] = $x;
					$file['links'] = ($show_link ? $_POST[$fieldName."_link".$file_name[2]] : "");
				}
				
				$v_upload['items'][] = $file;
			}
		}
		
		$files = '[]';
		if(sizeof($v_upload['items']) > 0)
		{
			$o_query = $o_main->db->query("SELECT * FROM accountinfo");
			$v_accountinfo = $o_query ? $o_query->row_array() : array();
			
			$s_response = APIconnectAccount("account_authenticate", $v_accountinfo['accountname'], $v_accountinfo['password']);
			$v_response = json_decode($s_response, TRUE);
			
			$v_upload['data'] = json_encode(array('action'=>'handle_file'));
			$v_upload['items'] = json_encode($v_upload['items']);
			$v_upload['username'] = $_COOKIE['username'];
			$v_upload['accountname'] = $v_accountinfo['accountname'];
			$v_upload['token'] = $v_response['token'];
			
			//call api
			$ch = curl_init($fw_session['content_server_api_url']);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $v_upload);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$s_response = curl_exec($ch);
			
			if($s_response !== false && $s_response != "")
			{
				$v_response = json_decode($s_response, true);
				if(isset($v_response['status']) && 1 == $v_response['status'])
				{
					$files = $v_response['items'];
				}
				if(isset($v_response['errors']) && 0 < sizeof($v_response['errors']))
				{
					foreach($v_response['errors'] as $s_error) $error_msg["error_".count($error_msg)] = $s_error;
				}
			} else {
				$error_msg["error_".count($error_msg)] = "Error occurred handling request";
			}
		}
		
		$fields[$fieldPos][6][$this->langfields[$a]] = $files;
	} else {
		// Handle local files
		$files = array();
		if(array_key_exists($fieldName."_name",$_POST))
		{
			$quota_bytes = 0;
			$upload_total_bytes = 0;
			$found_new_files = false;
			if(is_file($account_path."/uploads/_size.txt"))
			{
				$upload_total_bytes = intval(file_get_contents($account_path."/uploads/_size.txt"));
			}
			$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
			$o_query = $o_main->db->get_where('session_framework', $v_param);
			if($o_query && $o_row = $o_query->row()) $quota_bytes = $o_row->upload_quota;
			
			foreach($_POST[$fieldName."_name"] as $key => $item)
			{
				$file = array();
				$delete_file = $delete_file_fail = false;
				if(strpos($item,"process|") !== false)
				{
					// new uploads or delete old files
					$found_new_files = true;
					if($upload_total_bytes >= $quota_bytes) continue;
					$file_name = explode("|",$item); //process|upload_id|counter|filename
					$file[0] = $file_name[3];
					
					$file_obj = explode("|",$_POST[$fieldName."_file".$file_name[2]]); //process|upload_id|img_src
					if($file_obj[0] == "delete")
					{
						$delete_file = true;
						if(is_file($account_path."/".$file_obj[1]))
						{
							$uploads_dir = "/uploads/";
							if(strpos($file_obj[1],"uploads/protected/")!==false) $uploads_dir = "/uploads/protected/";
							if(strpos($file_obj[1],"uploads/storage/")!==false) $uploads_dir = "/uploads/storage/";
							$remove_path = str_replace($account_path.$uploads_dir,"",dirname($account_path."/".$file_obj[1]));
							
							unlink($account_path."/".$file_obj[1]);
							if(is_file($account_path."/".$file_obj[1]))
							{
								$delete_file_fail = true;
							} else {
								// remove directory
								$remove_path = explode("/",$remove_path);
								while(count($remove_path)>0)
								{
									$remove_dir = array_pop($remove_path);
									rmdir($account_path.$uploads_dir.implode("/",$remove_path)."/".$remove_dir);
								}
							}
						}
					} else if($file_obj[0] == "process")
					{
						$uploads_dir = "uploads/";
						if($protected or $img_size[3] == "p") $uploads_dir .= "protected/";
						$file_path = $uploads_dir.$file_obj[1]."/".$file_name[3];
						$file_path_encoded = $uploads_dir.$file_obj[1]."/".rawurlencode($file_name[3]);
						
						mkdir_recursive(dirname($account_path."/".$file_path));
						$file_obj[2] = rawurldecode($file_obj[2]);
						copy($account_path."/".$file_obj[2], $account_path."/".$file_path);
						
						if(!is_file($account_path."/".$file_path))
						{
							$error_msg["error_".count($error_msg)] = /*$formText_ThereWasProblemUploadingFile_fieldtype*/"There was problem uploading file".": ".$file_name[3];
						}
						if($b_remove_original && $file_obj[2] != "")
						{
							$l_i = 0;
							$s_delete_dir = dirname($account_path."/".$file_obj[2]);
							while(is_dir($s_delete_dir))
							{
								$l_i++;
								foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($s_delete_dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path)
								{
									$path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
								}
								rmdir($s_delete_dir);
								if($l_i > 5)
								{
									$error_msg["error_".count($error_msg)] = /*$formText_StorageFileWasNotDeleted_fieldtype*/"Storage file was not deleted".": ".$file_obj[2];
									break;
								}
							}
							if(!is_file($account_path."/".$file_obj[2]))
							{
								$o_main->db->query('delete from uploads where id = ?', array($file_name[1]));
							}
						}
						$file_path = $file_path_encoded;
					} else {
						// error: incorrect parameters for file
						$file_path = $_POST[$fieldName."_file".$file_name[2]];
					}
					$file[1] = array($file_path);
					
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$file_name[2]];
						}
					}
					$file[2] = $x;
					$file[3] = ($show_link ? $_POST[$fieldName."_link".$file_name[2]] : "");
					$file[4] = $file_name[1];
				} else {
					// old files
					$file_name = explode("|",$item); //process|upload_id|counter|filename
					$file[0] = $file_name[3];
					$file[1] = array($_POST[$fieldName."_file".$file_name[2]]);
					
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$file_name[2]];
						}
					}
					$file[2] = $x;
					$file[3] = ($show_link ? $_POST[$fieldName."_link".$file_name[2]] : "");
					$file[4] = $file_name[1];
				}
				
				if(!$delete_file || ($delete_file && $delete_file_fail))
				{
					if($delete_file && $delete_file_fail) $error_msg["error_".count($error_msg)] = /*$formText_FollowingFileWasNotDeleted_fieldtype*/"Following file was not deleted".": ".$file_name[3];
					$files[] = $file;
				}
			}
			if($found_new_files and $upload_total_bytes >= $quota_bytes)
			{
				$error_msg["error_".count($error_msg)] = "You have reached your file storage limit";//$formText_YouHaveReachedYourFileStorageLimit_fieldtype;
			}
		}
		
		if(count($files) > $file_count_limit)
		{
			$error_msg["error_".count($error_msg)] = /*$formText_YouHaveUploadedFilesMoreThan_fieldtype*/"You have uploaded files more than".": ".$file_count_limit;
		}
		
		$fields[$fieldPos][6][$this->langfields[$a]] = json_encode($files);
		
		$upload_total_bytes = dirsizeexec($account_path."/uploads/");
		file_put_contents($account_path."/uploads/_size.txt",$upload_total_bytes);
	}
}