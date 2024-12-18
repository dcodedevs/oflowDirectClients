<?php
$videoArray = array();
set_time_limit(500);
$account_path = realpath(__DIR__."/../../../../../");
if(!function_exists("dirsizeexec")) include(__DIR__."/fn_dirsizeexec.php");
if(!function_exists("mkdir_recursive")) include(__DIR__."/fn_mkdir_recursive.php");
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../includes/APIconnect.php");

$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
$o_query = $o_main->db->get_where('session_framework', $v_param);
$fw_session = $o_query ? $o_query->row_array() : array();

if($fields[$fieldPos][9]==1)
{
	// $fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
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
	
	if(isset($fw_session['content_server_api_url']) && '' != $fw_session['content_server_api_url'])
	{
		// Handle on CDN server
		$v_upload = array(
			'upload_quota' => 0,
			'file_count_limit' => $file_count_limit,
			'items' => array()
		);
		$files = array();
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
			
			$files = '[]';
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
		} else {
			$files = '[]';
		}
		$files = json_decode($files, TRUE);
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
	
		$upload_total_bytes = dirsizeexec($account_path."/uploads/");
		file_put_contents($account_path."/uploads/_size.txt",$upload_total_bytes);
	}
}

if($fields[$fieldPos][9]==1)
{
	// $fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
} else {
	if($fields[$fieldPos][11] == '') $fields[$fieldPos][11] = 'T1:0,0';
	list($type, $resize_codes) = explode(":",strtolower($fields[$fieldPos][11]),2);
	list($fieldtype, $limit) = explode(',',$type);
	$image_count_limit = ($limit>0?$limit:1);
	if(!isset($resize_codes) or $resize_codes == '') $resize_codes = '0,0';
	$resize_codes = explode(":",$resize_codes);

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

	if(isset($fw_session['content_server_api_url']) && '' != $fw_session['content_server_api_url'])
	{
		// Handle on CDN server
		$v_upload = array(
			'upload_quota' => 0,
			'image_count_limit' => $image_count_limit,
			'items' => array()
		);
		if(array_key_exists($fieldName."_imagename",$_POST))
		{
			$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
			$o_query = $o_main->db->get_where('session_framework', $v_param);
			$fw_session = $o_query ? $o_query->row_array() : array();
			$v_upload['upload_quota'] = $fw_session['upload_quota'];
	
			foreach($_POST[$fieldName."_imagename"] as $key => $item)
			{
				$image = array(
					'action'=>'',
					'items'=>array()
				);
				if(strpos($item,"process|") !== false)
				{
					// new uploads or delete old images
					$focus_counter = 0;
					$image_name = explode("|",$item); //process|upload_id|counter|filename
					$image['filename'] = $image_name[3];
					$image['upload_id'] = $image_name[1];
					foreach($_POST[$fieldName."_img".$image_name[2]] as $variant => $img_item)
					{
						$img_obj = explode("|",$img_item); //process|upload_id|w,h,[a]c|orig_w|orig_h|img_src|[crop]x!0|y!0|w!0|h!0|rotate!0
						if($img_obj[0] == "delete")
						{
							$image['action'] = 'delete';
							$image['items'][] = $img_obj[1];
							
						} else if($img_obj[0] == "process")
						{
							$image['action'] = 'process';
							$img_size = explode(",",strtolower($img_obj[2]));
							$v_properties = array();
							$v_properties['width'] = $img_size[0];
							$v_properties['height'] = $img_size[1];
							$v_properties['options'] = $img_size[2];
							$v_properties['options_extra'] = $img_size[3];
							$v_properties['protected'] = ($protected or strpos($img_size[3],"p") !== false);
							$v_properties['path'] = $img_obj[5];
							for($l_tmp = 6; $l_tmp < sizeof($img_obj); $l_tmp++)
							{
								$v_tmp = explode("!",$img_obj[$l_tmp]);
								$v_properties['crop_'.$v_tmp[0]] = $v_tmp[1];
							}
							$image['items'][] = $v_properties;
						}
					}
					
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
						}
					}
					$image['labels'] = $x;
					$image['links'] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");
					$image['focus'] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");
					$image['remove_original'] = $b_remove_original;
				} else {
					// old images
					$image_name = explode("|",$item); //process|upload_id|counter|filename
					$image['filename'] = $image_name[3];
					$image['upload_id'] = $image_name[1];
					$image['action'] = 'handled_items';
					$image['items'] = $_POST[$fieldName."_img".$image_name[2]];
					
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
						}
					}
					$image['labels'] = $x;
					$image['links'] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");
					$image['focus'] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");
				}
				
				$v_upload['items'][] = $image;
			}
		}
	
		if(sizeof($v_upload['items'])>0)
		{
			$o_query = $o_main->db->query("SELECT * FROM accountinfo");
			$v_accountinfo = $o_query ? $o_query->row_array() : array();
			
			$s_response = APIconnectAccount("account_authenticate", $v_accountinfo['accountname'], $v_accountinfo['password']);
			$v_response = json_decode($s_response, TRUE);
			
			$v_upload['data'] = json_encode(array('action'=>'handle_image'));
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
			
			$images = '[]';
			if($s_response !== false && $s_response != "")
			{
				$v_response = json_decode($s_response, true);
				if(isset($v_response['status']) && 1 == $v_response['status'])
				{
					$images = $v_response['items'];
				}
				if(isset($v_response['errors']) && 0 < sizeof($v_response['errors']))
				{
					foreach($v_response['errors'] as $s_error) $error_msg["error_".count($error_msg)] = $s_error;
				}
			} else {
				$error_msg["error_".count($error_msg)] = "Error occurred handling request";
			}
		} else {
			$images = '[]';
		}
		$images = json_decode($images, TRUE);
	} else {
		// Handle local files
		$images = array();
		if(array_key_exists($fieldName."_imagename",$_POST))
		{
			$quota_bytes = 0;
			$upload_total_bytes = 0;
			$found_new_images = false;
			if(is_file($account_path."/uploads/_size.txt"))
			{
				$upload_total_bytes = intval(file_get_contents($account_path."/uploads/_size.txt"));
			}
			$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
			$o_query = $o_main->db->get_where('session_framework', $v_param);
			if($o_query && $o_row = $o_query->row()) $quota_bytes = $o_row->upload_quota;
	
			foreach($_POST[$fieldName."_imagename"] as $key => $item)
			{
				$image = array();
				$delete_file = $delete_file_fail = false;
				if(strpos($item,"process|") !== false)
				{
					// new uploads or delete old images
					$s_original_file = "";
					$focus_counter = 0;
					$found_new_images = true;
					if($upload_total_bytes >= $quota_bytes) continue;
					$image_name = explode("|",$item); //process|upload_id|counter|filename
					$image[0] = $image_name[3];
					$x = array();
					foreach($_POST[$fieldName."_img".$image_name[2]] as $variant => $img_item)
					{
						$img_obj = explode("|",$img_item); //process|upload_id|w,h,[a]c|orig_w|orig_h|img_src|[crop]x!0|y!0|w!0|h!0|rotate!0
						if($img_obj[0] == "delete")
						{
							$delete_file = true;
							if(is_file($account_path."/".$img_obj[1]))
							{
								$uploads_dir = "/uploads/";
								if(strpos($img_obj[1],"uploads/protected/")!==false) $uploads_dir = "/uploads/protected/";
								if(strpos($img_obj[1],"uploads/storage/")!==false) $uploads_dir = "/uploads/storage/";
								$img_path = str_replace($account_path.$uploads_dir,"",dirname($account_path."/".$img_obj[1]));
	
								unlink($account_path."/".$img_obj[1]);
								if(is_file($account_path."/".$img_obj[1]))
								{
									$delete_file_fail = true;
								} else {
									// remove directory
									$remove_path = explode("/",$img_path);
									while(count($remove_path)>0)
									{
										$remove_dir = array_pop($remove_path);
										rmdir($account_path.$uploads_dir.implode("/",$remove_path)."/".$remove_dir);
									}
								}
							}
						} else if($img_obj[0] == "process")
						{
							$img_size = explode(",",strtolower($img_obj[2]));
							$uploads_dir = "uploads/";
							if($protected or strpos($img_size[3],"p") !== false) $uploads_dir .= "protected/";
							$img_path = $uploads_dir.$img_obj[1]."/".$variant."/".$image_name[3];
							$img_path_encoded = $uploads_dir.$img_obj[1]."/".$variant."/".rawurlencode($image_name[3]);
	
							mkdir_recursive(dirname($account_path."/".$img_path));
							$img_obj[5] = rawurldecode($img_obj[5]);
							list($src_w, $src_h, $stype, $attr) = getimagesize($account_path."/".$img_obj[5]);
							$s_original_file = $img_obj[5];
	
							if(($img_size[0] == 0 && $img_size[1] == 0 ) ||
								(
								($src_w < $img_size[0] && $src_h < $img_size[1]) ||
								($src_w < $img_size[0] && $img_size[1] == 0) ||
								($img_size[0] == 0 && $src_h < $img_size[1])
								) && ($img_size[2] != "c" && $img_size[2] != "ac")
							)
							{
								copy($account_path."/".$img_obj[5], $account_path."/".$img_path);
							}
							else
							{
								if($img_size[0] == 0) $wratio = 0; else $wratio = $src_w / $img_size[0];
								if($img_size[1] == 0) $hratio = 0; else $hratio = $src_h / $img_size[1];
								$ratios = array($hratio, $wratio);
								if(($img_size[2] == "ac" || $img_size[2] == "c") && $hratio != 0 && $wratio != 0)
								{
									$denratio = min($ratios);
								} else if($img_size[2] == "m") {
									$denratio = min($ratios);
								} else {
									$denratio = max($ratios);
								}
								if($denratio == 0) $denratio = 1;
	
								$newwidth = ceil($src_w / $denratio);
								$newheight = ceil($src_h / $denratio);
								$src_x = $src_y = 0;
								// resize cropping in center
								if($img_size[2] == "ac")
								{
									$autowidth = $newwidth;
									$autoheight = $newheight;
									if($newwidth > $img_size[0])
									{
										$src_x = intval(($newwidth - $img_size[0])/2);
										//$src_w = $src_w - intval(($newwidth - $img_size[0]) * $denratio);
										$autowidth = $img_size[0];
									}
									if($newheight > $img_size[1])
									{
										$src_y = intval(($newheight - $img_size[1])/2);
										//$src_h = $src_h - intval(($newheight - $img_size[1]) * $denratio);
										$autoheight = $img_size[1];
									}
								}
	
								$imagick = new Imagick($account_path."/".$img_obj[5]);
								if($img_size[2] == "c")
								{
									$img_crop = array();
									$tmp = explode("!",$img_obj[6]);
									$img_crop[$tmp[0]] = $tmp[1];
									$tmp = explode("!",$img_obj[7]);
									$img_crop[$tmp[0]] = $tmp[1];
									$tmp = explode("!",$img_obj[8]);
									$img_crop[$tmp[0]] = $tmp[1];
									$tmp = explode("!",$img_obj[9]);
									$img_crop[$tmp[0]] = $tmp[1];
									$tmp = explode("!",$img_obj[10]);
									$img_crop[$tmp[0]] = $tmp[1];
	
									if($src_w < ($img_crop['width'] + $img_crop['x'])) $img_crop['x'] = $src_w - $img_crop['width'];
									if($src_h < ($img_crop['height'] + $img_crop['y'])) $img_crop['y'] = $src_h - $img_crop['height'];
									if($img_crop['x']<0) $img_crop['x'] = 0;
									if($img_crop['y']<0) $img_crop['y'] = 0;
	
									$imagick->cropImage($img_crop['width'], $img_crop['height'], $img_crop['x'], $img_crop['y']);
									$imagick->resizeImage($img_size[0], $img_size[1], imagick::FILTER_LANCZOS, 1);
	
									if(strpos($img_size[3],"f") !== false)
									{
										$tmp = explode(":",$_POST[$fieldName."_focus".$image_name[2]][$focus_counter]);
										$tmp[0] = floor(($tmp[0] - $img_crop['x'])/$denratio);
										$tmp[1] = floor(($tmp[1] - $img_crop['y'])/$denratio);
										if($tmp[0]>$img_size[0]) $tmp[0] = $img_size[0];
										if($tmp[1]>$img_size[1]) $tmp[1] = $img_size[1];
										if($tmp[0]<0) $tmp[0] = 0;
										if($tmp[1]<0) $tmp[1] = 0;
										$_POST[$fieldName."_focus".$image_name[2]][$focus_counter] = implode(":",$tmp);
										$focus_counter++;
									}
								} else {
									$imagick->resizeImage($newwidth, $newheight, imagick::FILTER_LANCZOS, 1);
									$tmp = explode(":",$_POST[$fieldName."_focus".$image_name[2]][$focus_counter]);
									$tmp[0] = floor($tmp[0]/$denratio);
									$tmp[1] = floor($tmp[1]/$denratio);
									if($img_size[2] == "ac")
									{
										$imagick->cropImage($autowidth, $autoheight, $src_x, $src_y);
										$tmp[0] = $tmp[0] - $src_x;
										$tmp[1] = $tmp[1] - $src_y;
									}
									if(strpos($img_size[3],"f") !== false)
									{
										if($tmp[0]>$img_size[0]) $tmp[0] = $img_size[0];
										if($tmp[1]>$img_size[1]) $tmp[1] = $img_size[1];
										if($tmp[0]<0) $tmp[0] = 0;
										if($tmp[1]<0) $tmp[1] = 0;
										$_POST[$fieldName."_focus".$image_name[2]][$focus_counter] = implode(":",$tmp);
										$focus_counter++;
									}
								}
								$imagick->writeImage($account_path."/".$img_path);
							}
							if(!is_file($account_path."/".$img_path))
							{
								$error_msg["error_".count($error_msg)] = /*$formText_ThereWasProblemUploadingFile_fieldtype*/"There was problem uploading file".": ".$image_name[3]." (".$variant.")";
							}
							$img_path = $img_path_encoded;
						} else {
							// error: incorrect parameters for image
							$img_path = $img_item;
						}
						$x[] = $img_path;
					}
					$image[1] = $x;
	
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
						}
					}
					$image[2] = $x;
					$image[3] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");
					$image[4] = $image_name[1];
					$image[5] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");
	
					if($b_remove_original && $s_original_file != "")
					{
						$l_i = 0;
						$s_delete_dir = dirname($account_path."/".$s_original_file);
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
								$error_msg["error_".count($error_msg)] = /*$formText_StorageFileWasNotDeleted_fieldtype*/"Storage file was not deleted".": ".$s_original_file;
								break;
							}
						}
						if(!is_file($account_path."/".$s_original_file))
						{
							$o_main->db->query('delete from uploads where id = ?', array($image_name[1]));
						}
						$s_original_file = "";
					}
	
				} else {
					// old images
					$image_name = explode("|",$item); //process|upload_id|counter|filename
					$image[0] = $image_name[3];
					$image[1] = $_POST[$fieldName."_img".$image_name[2]];
	
					$x = array();
					if($show_text)
					{
						foreach($output_languages as $lid => $value)
						{
							$x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
						}
					}
					$image[2] = $x;
					$image[3] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");
					$image[4] = $image_name[1];
					$image[5] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");
				}
	
				if(!$delete_file || ($delete_file && $delete_file_fail))
				{
					if($delete_file && $delete_file_fail) $error_msg["error_".count($error_msg)] = /*$formText_FollowingFileWasNotDeleted_fieldtype*/"Following file was not deleted".": ".$image_name[3];
					$images[] = $image;
				}
			}
			if($found_new_images and $upload_total_bytes >= $quota_bytes)
			{
				$error_msg["error_".count($error_msg)] = "You have reached your file storage limit";//$formText_YouHaveReachedYourFileStorageLimit_fieldtype;
			}
		}
	
		if(count($images) > $image_count_limit)
		{
			$error_msg["error_".count($error_msg)] = /*$formText_YouHaveUploadedImagesMoreThan_fieldtype*/"You have uploaded images more than".": ".$image_count_limit;
		}
	
		$upload_total_bytes = dirsizeexec($account_path."/uploads/");
		file_put_contents($account_path."/uploads/_size.txt",$upload_total_bytes);
	}
}

$videoArray[0] = $_POST[$fieldName];
if(count($files) > 0){
    $videoArray[0] = $files;
}
$video_settings = array();
$video_settings[0] = intval($_POST[$fieldName."_settings_autoplay"]);
$video_settings[1] = intval($_POST[$fieldName."_settings_related"]);
$video_settings[2] = intval($_POST[$fieldName."_settings_controls"]);
$video_settings[3] = intval($_POST[$fieldName."_settings_loop"]);
$video_settings[4] = intval($_POST[$fieldName."_settings_muted"]);
$video_settings[5] = intval($_POST[$fieldName."_settings_showinfo"]);
$videoArray[1] = $video_settings;
if(count($images) > 0){
    $videoArray[2] = $images;
}


$fields[$fieldPos][6][$this->langfields[$a]] = json_encode($videoArray);
