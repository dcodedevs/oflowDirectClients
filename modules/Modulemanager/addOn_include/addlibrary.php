<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("ftp_file_put_content")) include(__DIR__."/../input/includes/ftp_commands.php");
if(!function_exists("get_developer_access")) include(__DIR__."/../input/includes/fn_get_developer_access.php");
if(!function_exists("get_folder_structure_checksum")) include(__DIR__."/fn_get_folder_structure_checksum.php");
$serverpath = rtrim(realpath(__DIR__.'/../../../../../'),'/').'/';
//error_reporting(E_ERROR | E_WARNING | E_PARSE); ini_set("display_errors", 1);

if(isset($_POST['module']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	$o_query = $o_main->db->query("SELECT * FROM accountinfo");
	$v_accountinfo = $o_query ? $o_query->row_array() : array();
	
	if(trim($_POST['newmodulename']) == '')
	{
		$newmodulename = str_replace("/","",substr($_POST['dir'],strrpos(substr($_POST['dir'],0,strlen($_POST['dir']) -1),"/")));
	} else {
		$newmodulename = trim($_POST['newmodulename']);
	}
	
	//Allow alphabetic characters only and do uppercase first
	$nameslit = explode(" ",trim(preg_replace("/[^A-Za-z0-9]+/", "", $newmodulename)));
	$newmodulename = "";
	foreach($nameslit as $item) $newmodulename .= ucfirst($item);
	
	$source = $serverpath.str_replace($serverpath,'',$_POST['dir']);
	$gocopy = 0;
	if(is_dir($source) && $_POST['type'] != 'serverlibrary')
	{
		$gocopy = 1;
	} else {
		$dirtest = ftp_ext_get_filelist('',$_POST['dir']);
		if(count($dirtest) > 0)
			$gocopy = 2;
	}
	
	if($gocopy > 0)
	{
		$o_query = $o_main->db->query('SELECT id FROM moduledata WHERE name = ?', array($newmodulename));
		if(!$o_query || ($o_query && $o_query->num_rows()==0))
		{
			$destination =  "/modules/".$newmodulename;
			$v_row = array();
			$o_query = $o_main->db->query('SELECT MAX(ordernr) ordernr FROM moduledata');
			if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
			
			$uniqueID = intval($_POST['module_config_module_id']);
			$o_query = $o_main->db->query("SELECT uniqueID FROM moduledata WHERE uniqueID = '".$o_main->db->escape_str($uniqueID)."'");
			if($o_query && $o_query->num_rows()>0)
			{
				$o_query = $o_main->db->query("SELECT MAX(uniqueID) uniqueID FROM moduledata".(10000 <= $uniqueID ? "" : " WHERE uniqueID < 10000"));
				$v_max = $o_query ? $o_query->row_array() : array();
				$uniqueID = intval($v_max['uniqueID']) + 1;
			}
			$s_sql_extra = ", uniqueID = '".$o_main->db->escape_str($uniqueID)."'";
			if(isset($_POST['module_config_local'])) $s_sql_extra .= ", local_module = 1";
			if(isset($_POST['module_config_custom'])) $s_sql_extra .= ", custom_module = 1";
			if('' != trim($_POST['module_config_mode'])) $s_sql_extra .= ", modulemode = '".$o_main->db->escape_str($_POST['module_config_mode'])."'";
			
			
			$o_main->db->query("insert into moduledata set name = '".$o_main->db->escape_str($newmodulename)."', ordernr = '".$o_main->db->escape_str(1+$v_row['ordernr'])."'".$s_sql_extra);
			$uniqueID = $o_main->db->insert_id();
			
			if(!$o_main->db->field_exists('uniqueID', 'moduledata'))
			{
				$o_main->db->simple_query("ALTER TABLE moduledata ADD uniqueID INT(11) NOT NULL AFTER id");
				$o_main->db->simple_query("ALTER TABLE moduledata CHANGE id id INT(11) NOT NULL");
				$o_main->db->simple_query("update moduledata set uniqueID = id");
				$o_main->db->simple_query("ALTER TABLE moduledata DROP PRIMARY KEY, ADD PRIMARY KEY(uniqueID)");
				$o_main->db->simple_query("ALTER TABLE moduledata CHANGE uniqueID uniqueID INT(11) NOT NULL AUTO_INCREMENT");
			}
			
			$o_main->db->query("update moduledata set id = ? where uniqueID = ?", array($uniqueID, $uniqueID));
			if($gocopy == 1)
			{
				ftp_copy_directory($source,$destination,1);
			}
			else
			{
 				$destination_tmp = $serverpath."accounts/".$_POST['accountname']."/uploads/installtmp/".$newmodulename;
				$source_lib =$_POST['dir'];
				$source_tmp = $serverpath."accounts/".$_POST['accountname']."/uploads/installtmp/".$newmodulename;
				mkdir($destination_tmp,octdec(2777),true);
				set_time_limit(0);
				
				$copyreturn = ftp_ext_copy_directory($source_lib,$destination_tmp,1,"get");
				if($copyreturn == 1)
				{
					ftp_copy_directory($source_tmp,$destination,1);
					mm_remove_directory($source_tmp);
				} else {
					mm_remove_directory($source_tmp);
					echo "<br>FTP ERROR: Some files are not copied: <br>";
					print_r($copyreturn);exit;
				}
			}
			$extradir = __DIR__."/../../".$newmodulename;
			//create DB tables and fields
			if(is_file(__DIR__."/../../".$newmodulename."/input/includes/databaseFieldsCheck.php"))
			{
				include(__DIR__."/../../".$newmodulename."/input/includes/databaseFieldsCheck.php");
			}
			
			# Update Cache
			$fw_session = array();
			$v_param = array('companyaccessID' => $_POST['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
			$o_query = $o_main->db->get_where('session_framework', $v_param);
			if($o_query) $fw_session = $o_query->row_array();
			$menuaccess = json_decode($fw_session['cache_menu'], true);
			
			$dataUserAccess = array('data'=>array(0=>array('moduleAccesslevel'=>111,'useraccountaccess'=>1,'usercompanyaccess'=>1,'companyaccess'=>1)));
			$menuaccess[$newmodulename] = array($newmodulename, "module=".$newmodulename."&amp;moduleID=".$uniqueID."&amp;modulename=".$newmodulename."&amp;folder=input&amp;folderfile=input&amp;updatepath=1&amp;external=", 111, 'C', $dataUserAccess, 0);
			
			$o_main->db->update('session_framework', array('cache_menu' => json_encode($menuaccess)), $v_param);
			
			$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
			header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_POST['pageID']."&accountname=".$_POST['accountname']."&companyID=".$_POST['companyID']."&caID=".$_POST['caID']."&module=Modulemanager");
		} else {
			print "Error occured. Trying to add or duplicate existing module";
		}
	} else {
		print "Error occured. Module does not exists.";
	}
	exit;
}

else if(isset($_GET['update']))
{
	$ftperror = 0;
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	if($_POST['sourcemodule'] != $_POST['sourcemodule_original'])
	{
		print 'Choosen source mistmatch or not approved!';
		exit;
	}
	
	$b_make_backup = true;
	
	foreach($_POST as $key=>$value)
	{ 
		if(strpos($key,'update_folder_') !== false)
		{
			// Make backup
			if($b_make_backup)
			{
				$s_timestamp = date("Ymd_His");
				$sys_module = preg_replace("/[^A-Za-z0-9]+/", "", $_POST['updatemodule']);
				$accountpath = $serverpath."accounts/".preg_replace("/[^A-Za-z0-9]+/", "", $_GET['accountname'])."/";
				$destination_tmp = "uploads/installtmp/".$sys_module."_input_bkp.tar.gz";
				mkdir($accountpath."uploads/installtmp",octdec(2777),true);
				if(is_file($accountpath.$destination_tmp)) unlink($accountpath.$destination_tmp);
				exec('tar -czf '.$accountpath.$destination_tmp.' -C '.$accountpath."modules/".$sys_module."/ input", $out);
				// Dot in front only for FTP rename
				//ftp_rename_file($destination_tmp, "./backup/".$sys_module."_input_".$s_timestamp.".tar.gz");
				ftp_copy($accountpath.$destination_tmp, "./backup/".$sys_module."_input_".$s_timestamp.".tar.gz");
				unlink($accountpath.$destination_tmp);
				$b_make_backup = false;
			}
			// Handle input update
			$items = explode('::',$value);
			foreach($items as $item)
			{
				list($from,$to,$update_subfolders) = explode(':',$item);
				$destination_tmp = $serverpath."accounts/".$_GET['accountname']."/uploads/installtmp/".$_POST['updatemodule']."/".$to;
				$source_tmp = "/uploads/installtmp/".$_POST['updatemodule']."/".$to;
				mkdir($destination_tmp,octdec(2777),true);
				$destination = "/modules/".$_POST['updatemodule']."/".$to;
				if($update_subfolders == 0)
				{
					$returnfiles = ftp_ext_copy_singlefiles($from,$destination_tmp);
					if($returnfiles == 1)
					{
						ftp_delete_directory($destination,$update_subfolders);
						ftp_copy_singlefiles($serverpath."accounts/".$_GET['accountname'].$source_tmp,$destination);
					} else {
						echo "<br>ERROR IN FTP:";print_r($returnfiles);	
						$ftperror = 1;
					}
				} else {
					$returnfiles = ftp_ext_copy_directory($from,$destination_tmp,$update_subfolders);
					if($returnfiles == 1)
					{
						ftp_delete_directory($destination,$update_subfolders);
						ftp_copy_directory($serverpath."accounts/".$_GET['accountname'].$source_tmp,$destination,$update_subfolders);//exit;
					} else {
						echo "<br>ERROR IN FTP test:";print_r($returnfiles);	
						$ftperror = 1;
					}
				}
			}
			$destination_tmp = $serverpath."accounts/".$_GET['accountname']."/uploads/installtmp/".$sys_module;
			mm_remove_directory($destination_tmp);
		}
	}
	//echo "ftperror = ".$ftperror;
	if($ftperror == 0)
	{
		$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		if(isset($_POST['update_all']))
		{
			echo '{"status":1}';
		} else {
			header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
		}
	}
	exit;
}

if(isset($_GET['updateobject']))
{
	if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
	$v_r = array("/",".");
	$error_msg = array();
	$accountpath = rtrim(realpath(__DIR__.'/../../../'),'/').'/';
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	if(!is_dir($accountpath."backup")) mkdir($accountpath."backup",octdec(2777),true);
	if(!is_dir($accountpath."backup"))
	{
		$error_msg["error_".count($error_msg)] = "Backup is not created. Permission problem.";
	} else {
		if($_GET['updateobject'] == "fw" || $_GET['updateobject'] == "frameworks")
		{
			$s_element = 'fw';
			$s_updated_element = 'Framework';
			$s_temp_path = $accountpath."uploads/installtmp/fw";
			if(is_dir($accountpath.$s_element))
			{
				$s_lib_category = "frameworks";
				$s_lib_element = $s_element;
				if(is_file($accountpath.$s_element."/central.lib")) $s_lib_category = trim(file_get_contents($accountpath.$s_element."/central.lib"));
				if($s_lib_category == "") $s_lib_category = "frameworks";
				$v_central_lib_conf = explode('/', $s_lib_category);
				if(count($v_central_lib_conf)>1)
				{
					$s_lib_category = $v_central_lib_conf[0];
					$s_lib_element = $v_central_lib_conf[1];
				}
				
				mkdir($s_temp_path,octdec(2777),true);
				ftp_ext_copy_directory("/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,$s_temp_path,1);
				
				//verify copied object checksum
				$s_local_folder_checksum = get_folder_structure_checksum($s_temp_path, TRUE);
				$v_data = array
				(
					'action'=>'verify_object_checksum',
					'path'=>"/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,
					'checksum'=>$s_local_folder_checksum/*,
					'excludes'=>array()*/
				);
				$v_data = array('data'=>json_encode($v_data));
				$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
				
				$v_data = json_decode($response,true);
				$s_version = get_folder_version($s_temp_path);
				if($s_version != "" && isset($v_data['status']) && $v_data['status'] == 1)
				{
					// Make backup
					$s_timestamp = date("Ymd_His");
					$destination_tmp = "uploads/installtmp/".$s_element.".tar.gz";
					mkdir(dirname($accountpath.$destination_tmp),octdec(2777),true);
					if(is_file($accountpath.$destination_tmp)) unlink($accountpath.$destination_tmp);
					exec('tar -czf '.$accountpath.$destination_tmp.' -C '.$accountpath.$s_element."/ ../".$s_element);
					ftp_copy($accountpath.$destination_tmp, '/backup/'.$s_element.'_'.$s_timestamp.'.tar.gz');
					// Dot in front only for FTP rename
					ftp_rename_file("./".$s_element, "./".$s_element."_".$s_timestamp);
					
					if(!is_dir($accountpath.$s_element))
					{
						ftp_copy_directory($s_temp_path,"/".$s_element,1);
						$s_checksum_tmp = get_folder_structure_checksum($s_temp_path, TRUE);
						$s_checksum = get_folder_structure_checksum($accountpath.$s_element, TRUE);
						if($s_checksum_tmp == $s_checksum)
						{
							if(is_file($accountpath."backup/".$s_element."_".$s_timestamp.".tar.gz"))
							{
								ftp_delete_directory("/".$s_element."_".$s_timestamp,1);
								unlink($accountpath.$destination_tmp);
							}
						} else {
							$error_msg["error_".count($error_msg)] = "Checksum failure.";
							// Dot in front only for FTP rename
							ftp_rename_file("./".$s_element."_".$s_timestamp, "./".$s_element);
						}
					} else {
						$error_msg["error_".count($error_msg)] = "Framework was not updated by technical problem.";
					}
				} else {
					$error_msg["error_".count($error_msg)] = "Framework cannot be found in library or copied partly.";
				}
				mm_remove_directory($s_temp_path);
			} else {
				$error_msg["error_".count($error_msg)] = "Framework cannot be found in account.";
			}
		}
		
		if($_GET['updateobject'] == "account_elements")
		{
			$s_element = str_replace($v_r,"",$_GET[$_GET['updateobject']]);
			$s_updated_element = $s_element;
			$s_temp_path = $accountpath."uploads/installtmp/".$s_element;
			if(is_dir($accountpath.$s_element))
			{
				$s_lib_category = "account_elements";
				$s_lib_element = $s_element;
				if(is_file($accountpath.$s_element."/central.lib")) $s_lib_category = trim(file_get_contents($accountpath.$s_element."/central.lib"));
				if($s_lib_category == "") $s_lib_category = "account_elements";
				$v_central_lib_conf = explode('/', $s_lib_category);
				if(count($v_central_lib_conf)>1)
				{
					$s_lib_category = $v_central_lib_conf[0];
					$s_lib_element = $v_central_lib_conf[1];
				}
				
				mkdir($s_temp_path,octdec(2777),true);
				ftp_ext_copy_directory("/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,$s_temp_path,1);
				
				//verify copied object checksum
				$s_local_folder_checksum = get_folder_structure_checksum($s_temp_path, TRUE);
				$v_data = array
				(
					'action'=>'verify_object_checksum',
					'path'=>"/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,
					'checksum'=>$s_local_folder_checksum/*,
					'excludes'=>array()*/
				);
				$v_data = array('data'=>json_encode($v_data));
				$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
				
				$v_data = json_decode($response,true);
				$s_version = get_folder_version($s_temp_path);
				if($s_version != "" && isset($v_data['status']) && $v_data['status'] == 1)
				{
					// Make backup
					$s_timestamp = date("Ymd_His");
					$destination_tmp = "uploads/installtmp/".$s_element.".tar.gz";
					mkdir(dirname($accountpath.$destination_tmp),octdec(2777),true);
					if(is_file($accountpath.$destination_tmp)) unlink($accountpath.$destination_tmp);
					exec('tar -czf '.$accountpath.$destination_tmp.' -C '.$accountpath.$s_element."/ ../".$s_element);
					ftp_copy($accountpath.$destination_tmp, '/backup/'.$s_element.'_'.$s_timestamp.'.tar.gz');
					// Dot in front only for FTP rename
					ftp_rename_file("./".$s_element, "./".$s_element."_".$s_timestamp);
					
					if(!is_dir($accountpath.$s_element))
					{
						ftp_copy_directory($s_temp_path,"/".$s_element,1);
						$s_checksum_tmp = get_folder_structure_checksum($s_temp_path, TRUE);
						$s_checksum = get_folder_structure_checksum($accountpath.$s_element, TRUE);
						if($s_checksum_tmp == $s_checksum)
						{
							if(is_file($accountpath."backup/".$s_element."_".$s_timestamp.".tar.gz"))
							{
								ftp_delete_directory("/".$s_element."_".$s_timestamp,1);
								unlink($accountpath.$destination_tmp);
							}
						} else {
							$error_msg["error_".count($error_msg)] = "Checksum failure.";
							// Dot in front only for FTP rename
							ftp_rename_file("./".$s_element."_".$s_timestamp, "./".$s_element);
						}
					} else {
						$error_msg["error_".count($error_msg)] = "Object was not updated by technical problem.";
					}
				} else {
					$error_msg["error_".count($error_msg)] = "Object cannot be found in library or copied partly.";
				}
				mm_remove_directory($s_temp_path);
			} else {
				$error_msg["error_".count($error_msg)] = "Object cannot be found in account.";
			}
		}
		
		if($_GET['updateobject'] == "sys_module")
		{
			$sys_module = str_replace($v_r,"",$_GET[$_GET['updateobject']]);
			$s_updated_element = $sys_module;
			$s_temp_path = $accountpath."uploads/installtmp/".$sys_module;
			if(is_dir($accountpath."modules/".$sys_module))
			{
				$s_lib_category = "";
				$s_lib_element = $sys_module;
				if(is_file($accountpath."modules/".$sys_module."/central.lib")) $s_lib_category = trim(file_get_contents($accountpath."modules/".$sys_module."/central.lib"));
				if($s_lib_category == "") $s_lib_category = "modules";
				$v_central_lib_conf = explode('/', $s_lib_category);
				if(count($v_central_lib_conf)>1)
				{
					$s_lib_category = $v_central_lib_conf[0];
					$s_lib_element = $v_central_lib_conf[1];
				}
				
				mkdir($s_temp_path,octdec(2777),true);
				ftp_ext_copy_directory("/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element, $s_temp_path,1);
				
				//verify copied object checksum
				$s_local_folder_checksum = get_folder_structure_checksum($s_temp_path, TRUE);
				$v_data = array
				(
					'action'=>'verify_object_checksum',
					'path'=>"/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,
					'checksum'=>$s_local_folder_checksum/*,
					'excludes'=>array()*/
				);
				$v_data = array('data'=>json_encode($v_data));
				$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
				
				$v_data = json_decode($response,true);
				$s_version = get_folder_version($s_temp_path);
				if($s_version != "" && isset($v_data['status']) && $v_data['status'] == 1)
				{
					// Make backup
					$s_timestamp = date("Ymd_His");
					$destination_tmp = "uploads/installtmp/".$sys_module.".tar.gz";
					mkdir(dirname($accountpath.$destination_tmp),octdec(2777),true);
					if(is_file($accountpath.$destination_tmp)) unlink($accountpath.$destination_tmp);
					exec('tar -czf '.$accountpath.$destination_tmp.' -C '.$accountpath."/modules/ ".$sys_module);
					ftp_copy($accountpath.$destination_tmp, '/backup/'.$sys_module.'_'.$s_timestamp.'.tar.gz');
					// Dot in front only for FTP rename
					ftp_rename_file("./modules/".$sys_module, "./modules/".$sys_module."_".$s_timestamp);
					
					if(!is_dir($accountpath."modules/".$sys_module))
					{
						ftp_copy_directory($s_temp_path,"/modules/".$sys_module,1);
						$s_checksum_tmp = get_folder_structure_checksum($s_temp_path, TRUE);
						$s_checksum = get_folder_structure_checksum($accountpath."modules/".$sys_module, TRUE);
						if($s_checksum_tmp == $s_checksum)
						{
							//create DB tables and fields
							$s_db_check_script = $accountpath."modules/".$sys_module."/input/includes/databaseFieldsCheck.php";
							if(is_file($s_db_check_script)) include($s_db_check_script);
							if(is_file($accountpath."backup/".$sys_module."_".$s_timestamp.".tar.gz"))
							{
								ftp_delete_directory("/modules/".$sys_module."_".$s_timestamp,1);
								unlink($accountpath.$destination_tmp);
							}
						} else {
							$error_msg["error_".count($error_msg)] = "Checksum failure.";
							// Dot in front only for FTP rename
							ftp_rename_file("./modules/".$sys_module."_".$s_timestamp, "./modules/".$sys_module);
						}
					} else {
						$error_msg["error_".count($error_msg)] = "Module was not updated by technical problem.";
					}
				} else {
					$error_msg["error_".count($error_msg)] = "Module cannot be found in library or copied partly.";
				}
				mm_remove_directory($s_temp_path);
			} else {
				$error_msg["error_".count($error_msg)] = "Module cannot be found in account.";
			}
		}
		
		if($_GET['updateobject'] == "customized_elements")
		{
			$s_element = str_replace($v_r,"",$_GET[$_GET['updateobject']]);
			$s_updated_element = $s_element;
			$s_temp_path = $accountpath."uploads/installtmp/".$s_element;
			if(is_dir($accountpath.'elementsCustomized/'.$s_element))
			{
				$s_lib_category = "account_elements";
				$s_lib_element = $s_element;
				if(is_file($accountpath.'elementsCustomized/'.$s_element."/central.lib")) $s_lib_category = trim(file_get_contents($accountpath.'elementsCustomized/'.$s_element."/central.lib"));
				if($s_lib_category == "") $s_lib_category = "account_elements";
				$v_central_lib_conf = explode('/', $s_lib_category);
				if(count($v_central_lib_conf)>1)
				{
					$s_lib_category = $v_central_lib_conf[0];
					$s_lib_element = $v_central_lib_conf[1];
				}
				
				mkdir($s_temp_path,octdec(2777),true);
				ftp_ext_copy_directory("/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,$s_temp_path,1);
				
				//verify copied object checksum
				$s_local_folder_checksum = get_folder_structure_checksum($s_temp_path, TRUE);
				$v_data = array
				(
					'action'=>'verify_object_checksum',
					'path'=>"/".$s_lib_category."/".$s_lib_element."/".str_replace($v_r,"",$_POST["sourceversion"])."/".$s_lib_element,
					'checksum'=>$s_local_folder_checksum/*,
					'excludes'=>array()*/
				);
				$v_data = array('data'=>json_encode($v_data));
				$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
				
				$v_data = json_decode($response,true);
				$s_version = get_folder_version($s_temp_path);
				if($s_version != "" && isset($v_data['status']) && $v_data['status'] == 1)
				{
					// Make backup
					$s_timestamp = date("Ymd_His");
					$destination_tmp = "uploads/installtmp/".$s_element.".tar.gz";
					mkdir(dirname($accountpath.$destination_tmp),octdec(2777),true);
					if(is_file($accountpath.$destination_tmp)) unlink($accountpath.$destination_tmp);
					exec('tar -czf '.$accountpath.$destination_tmp.' -C '.$accountpath.'elementsCustomized/'.$s_element."/ ../".$s_element);
					ftp_copy($accountpath.$destination_tmp, '/backup/'.$s_element.'_'.$s_timestamp.'.tar.gz');
					// Dot in front only for FTP rename
					ftp_rename_file("./elementsCustomized/".$s_element, "./elementsCustomized/".$s_element."_".$s_timestamp);
					
					if(!is_dir($accountpath.'elementsCustomized/'.$s_element))
					{
						ftp_copy_directory($s_temp_path,"/elementsCustomized/".$s_element,1);
						$s_checksum_tmp = get_folder_structure_checksum($s_temp_path, TRUE);
						$s_checksum = get_folder_structure_checksum($accountpath.'elementsCustomized/'.$s_element, TRUE);
						if($s_checksum_tmp == $s_checksum)
						{
							if(is_file($accountpath."backup/".$s_element."_".$s_timestamp.".tar.gz"))
							{
								ftp_delete_directory("/elementsCustomized/".$s_element."_".$s_timestamp,1);
								unlink($accountpath.$destination_tmp);
							}
						} else {
							$error_msg["error_".count($error_msg)] = "Checksum failure.";
							// Dot in front only for FTP rename
							ftp_rename_file("./elementsCustomized/".$s_element."_".$s_timestamp, "./elementsCustomized/".$s_element);
						}
					} else {
						$error_msg["error_".count($error_msg)] = "Object was not updated by technical problem.";
					}
				} else {
					$error_msg["error_".count($error_msg)] = "Object cannot be found in library or copied partly.";
				}
				mm_remove_directory($s_temp_path);
			} else {
				$error_msg["error_".count($error_msg)] = "Object cannot be found in account.";
			}
		}
	}
	
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	if(count($error_msg)==0)
	{
		if(isset($_POST['update_all_request'])) echo '["OK"]';
		$error_msg = array('info_0'=>'Element has been updated: '.$s_updated_element);
	}
	if(isset($_POST['update_all_request']))
	{
		$o_query = $o_main->db->get_where('session_framework', $v_param);
		$fw_session = $o_query ? $o_query->row_array() : array();
		$v_tmp = json_decode($fw_session['error_msg'], TRUE);
		if(sizeof($v_tmp)>0)
		{
			foreach($error_msg as $s_key => $s_value)
			{
				list($s_type, $s_rest) = explode('_', $s_key);
				$v_tmp[$s_type.'_'.count($v_tmp)] = $s_value;
			}
			$error_msg = $v_tmp;
		}
	}
	$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	
	if(!isset($_POST['update_all_request']))
	{
		header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	}
	$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	exit;
}


if(isset($_GET['lock_object']))
{
	$v_r = array("/",".");
	$error_msg = array();
	$accountpath = rtrim(realpath(__DIR__.'/../../../'),'/').'/';
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	$v_data = array
	(
		'action'=>'lock_element',
		'category'=>str_replace($v_r, '', $_POST['category']),
		'element'=>str_replace($v_r, '', $_POST['element']),
		'username'=>$_COOKIE['username'],
		'comment'=>$_POST['comment']
	);
	$v_data = array('data'=>json_encode($v_data));
	$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
			
	$v_data = json_decode($response,true);
	if(isset($v_data['status']) && $v_data['status'] == 1)
	{
		$error_msg = array('info_0'=>'Locked element: '.$_POST['element']);
	} else {
		$error_msg = array('error_0'=>$v_data['message']);
	}
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	exit;
}

if(isset($_GET['unlock_object']))
{
	$v_r = array("/",".");
	$error_msg = array();
	$accountpath = rtrim(realpath(__DIR__.'/../../../'),'/').'/';
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	list($s_category, $s_element) = explode(':', $_GET['unlock_object']);
	
	$v_data = array
	(
		'action'=>'unlock_element',
		'category'=>str_replace($v_r, '', $s_category),
		'element'=>str_replace($v_r, '', $s_element),
		'username'=>$_COOKIE['username']
	);
	$v_data = array('data'=>json_encode($v_data));
	$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
			
	$v_data = json_decode($response,true);
	if(isset($v_data['status']) && $v_data['status'] == 1)
	{
		$error_msg = array('info_0'=>'Unlocked element: '.$s_element);
	} else {
		$error_msg = array('error_0'=>$v_data['message']);
	}
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	exit;
}


else if(isset($_GET['updatemode']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	if(in_array($_GET['mode'],array("C","E","I","A","S","D")))
	{
		$o_main->db->query("update moduledata set modulemode = ? where name = ?", array($_GET['mode'], $_GET['updatemode']));
		
		# Update Cache
		$fw_session = array();
		$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_query = $o_main->db->get_where('session_framework', $v_param);
		if($o_query) $fw_session = $o_query->row_array();
		$menuaccess = json_decode($fw_session['cache_menu'], true);
		foreach($menuaccess as $key=>$value)
		{
			if($key == $_GET['updatemode'])
			{
				$menuaccess[$key][3] = $_GET['mode'];
			}
		}
		$o_main->db->update('session_framework', array('cache_menu' => json_encode($menuaccess)), $v_param);
	}
	
	$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	exit;
}


else if(isset($_GET['updatetype']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	$modulename = str_replace("/","",$_GET['updatetype']);
	$_GET['type'] = intval($_GET['type']);
	
	$o_main->db->query("update moduledata set type = ? where name = ?", array($_GET['type'], $modulename));
	
	$s_dir = __DIR__."/../../".$modulename."/input/settings/tables";
	if($o_handler = opendir($s_dir)) 
	{
		while(false !== ($s_file = readdir($o_handler)))
		{
			if($s_file!="." and $s_file!=".." && substr($s_file,-4)==".php")
			{
				$s_content = file_get_contents($s_dir."/".$s_file);
				$s_content = preg_replace('#\$moduledatatype ?= ?"[0-9]+";#', '$moduledatatype = "'.$_GET['type'].'";', $s_content);
				ftp_file_put_content("/modules/".$modulename."/input/settings/tables/".$s_file, $s_content);
			}
		}
		closedir($o_handler);
	}
	
	$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	exit;
}


else if(isset($_GET['updatestatus']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	$v_row = array();
	$o_query = $o_main->db->query('select deactivated from moduledata where name = ?', array($_GET['dir']));
	if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
	if($v_row['deactivated'] == 1)
		$deactivated = 0;
	else
		$deactivated = 1;
	
	$o_main->db->query("update moduledata set deactivated = ? where name = ?", array($deactivated, $_GET['dir']));
	
	# Update Cache
	$fw_session = array();
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$menuaccess = json_decode($fw_session['cache_menu'], true);
	foreach($menuaccess as $key=>$value)
	{
		if($key == $_GET['updatemode'])
		{
			$menuaccess[$key][5] = $deactivated;
		}
	}
	$o_main->db->update('session_framework', array('cache_menu' => json_encode($menuaccess)), $v_param);
	
	$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	exit;
}


else if(isset($_GET['editOrder']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	foreach($_POST['uniqueID'] as $key => $value)
	{
		$o_main->db->query("update moduledata set ordernr = ? where uniqueID = ?", array($key, $value));
	}
	
	# Update Cache
	$fw_session = array();
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$menuaccess = json_decode($fw_session['cache_menu'], true);
	$newmenuaccess = array();
	$o_query = $o_main->db->query('select name from moduledata order by modulemode, ordernr');
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $row)
	{
		$newmenuaccess[$row['name']] = $menuaccess[$row['name']];
	}
	$o_main->db->update('session_framework', array('cache_menu' => json_encode($newmenuaccess)), $v_param);
	
	$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
	exit;
}

else if(isset($_GET['searchcontent']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
    //Emptye searchlog to be able to identify when search are finished
	file_put_contents(dirname(__FILE__)."/../../../uploads/modulesearch.txt","");
	//update permissions to account
	$v_accountinfo = array();
    if($_POST['searchword'] != ''){
        $o_query = $o_main->db->query('SELECT * FROM accountinfo');
        if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();
        if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
        $response = APIconnectAccount("accountsearchcontent", $v_accountinfo['accountname'], $v_accountinfo['password'], array("SEARCHWORD"=>$_POST['searchword']));
        //print_r($response);
        header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager&includefile=search_account_files&waitresult=1");
    }
	else
        header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager&includefile=search_account_files");
	exit;
}

else if(isset($_GET['table_history'], $_POST['table_history']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	$b_recreate_trigger = (1 == $_POST['recreate_all_triggers']);
	
	$l_changes = 0;
	foreach($_POST['history_table'] as $l_key => $s_table)
	{
		$b_active = (1 == $_POST['history_table_sel'][$l_key]);
		
		$o_check = $o_main->db->query("SHOW TRIGGERS WHERE `TABLE` = '".$s_table."' AND EVENT = 'UPDATE' AND TIMING = 'AFTER' AND STATEMENT LIKE '%sys_content_history%'");
		$b_exists = ($o_check && $o_check->num_rows()>0);
		
		// Remove trigger
		if(($b_exists && $b_recreate_trigger) || (!$b_active && $b_exists))
		{
			$o_trigger = $o_main->db->query("SHOW TRIGGERS WHERE `TABLE` = '".$s_table."' AND (EVENT = 'UPDATE' OR EVENT = 'DELETE') AND TIMING = 'AFTER' AND STATEMENT LIKE '%sys_content_history%'");
			if($o_trigger && $o_trigger->num_rows()>0)
			foreach($o_trigger->result_array() as $v_trigger)
			{
				$o_update = $o_main->db->query("DROP TRIGGER `".$v_trigger['Trigger']."`;");
				if($o_update)
				{
					$l_changes++;
				} else {
					//echo $o_main->db->last_query()."<br>";
					//print_r($o_main->db->error());
					$error_msg['error_'.count($error_msg)] = "Cannot drop trigger `".$v_trigger['Trigger']."` on table `".$s_table."` (".$o_main->db->error().")";
				}
			}
		}
		// Add trigger
		if(($b_exists && $b_recreate_trigger) || ($b_active && !$b_exists))
		{
			$b_enabled = FALSE;
			$s_sql = "
			CREATE TRIGGER `".$s_table."_au` AFTER UPDATE ON `".$s_table."` FOR EACH ROW BEGIN
			DECLARE updated TEXT;
			SET @updated = '';
			SET @do_insert = 0;
			";
			
			$v_skip_fields = array(
				'created',
				'createdBy',
				'updated',
				'updatedBy',
				'name_sort',
			);
			$o_column = $o_main->db->query("SHOW COLUMNS FROM ".$s_table);
			if($o_column && $o_column->num_rows()>0)
			foreach($o_column->result_array() as $v_column)
			{
				if(in_array($v_column['Field'], $v_skip_fields)) continue;
				if('PRI' == $v_column['Key'])
				{
					$b_enabled = TRUE;
				}
				$s_sql .= "
				IF IFNULL(NEW.`".$v_column['Field']."`, '') <> IFNULL(OLD.`".$v_column['Field']."`, '') THEN
					SET @do_insert = 1;
					IF 0 < LENGTH(@updated) THEN
						SET @updated = CONCAT(@updated, ',');
					END IF;

					IF OLD.`".$v_column['Field']."` IS NULL THEN
						SET @updated = CONCAT(@updated, '\"".$v_column['Field']."\":', 'null');
					ELSE
						SET @updated = CONCAT(@updated, '\"".$v_column['Field']."\":', JSON_QUOTE(OLD.`".$v_column['Field']."`));
					END IF;
				END IF;";
				('' != $s_sql_columns ? ', ' : '')."'".$v_column['Field']."', OLD.`".$v_column['Field']."`";
			}
			$s_sql.="
			IF @do_insert = 1 THEN
				INSERT INTO `sys_content_history`
				SET
					id = NULL,
					created = NOW(),
					content_table = '".$s_table."',
					content_id = OLD.id,
					content_value = CONCAT('{', @updated, '}');
			END IF;
			END";
			
			if($b_enabled)
			{
				$o_update = $o_main->db->query($s_sql);
				if($o_update)
				{
					$l_changes++;
				} else {
					//echo $o_main->db->last_query()."<br>";
					//print_r($o_main->db->error());
					$error_msg['error_'.count($error_msg)] = "Cannot add trigger `".$v_trigger['Trigger']."` on table `".$s_table."` (".$o_main->db->error().")";
				}
				
				$s_sql = "
				CREATE TRIGGER `".$s_table."_ad` AFTER DELETE ON `".$s_table."` FOR EACH ROW BEGIN
				DELETE FROM `sys_content_history` WHERE content_id = OLD.id AND content_table = '".$s_table."';
				END";
				$o_update = $o_main->db->query($s_sql);
				if($o_update)
				{
					$l_changes++;
				} else {
					//echo $o_main->db->last_query()."<br>";
					//print_r($o_main->db->error());
					$error_msg['error_'.count($error_msg)] = "Cannot add trigger `".$v_trigger['Trigger']."` on table `".$s_table."` (".$o_main->db->error().")";
				}
			}
		}
	}
	//foreach($o_main->db->queries as $s_query) echo $s_query."<br><br>";die('asdf');
	if(0 == count($error_msg))
	{
		if(0 < $l_changes)
		{
			$error_msg = array('info_0'=>'Changes has been made successfylly');
		} else {
			$error_msg = array('warn_0'=>'No changes has been detected and nothing was changed');
		}
	}
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager&includefile=table_history");
	exit;
}

else if(isset($_GET['resetpermissions']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	//update permissions to account
	$v_accountinfo = array();
	$o_query = $o_main->db->query('SELECT * FROM accountinfo');
	if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();
	if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
	$response = APIconnectAccount("accountresetpermissions", $v_accountinfo['accountname'], $v_accountinfo['password'], array());
	//print_r($response);
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager&restore=1");
	exit;
}


else if(isset($_GET['updateencoding']))
{
	if(get_developer_access() < 20)
	{
		print 'Access denied!';
		exit;
	}
	
	//update permissions to account
	$v_accountinfo = array();
	$o_query = $o_main->db->query('SELECT * FROM accountinfo');
	if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();
	if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
	$response = APIconnectAccount("accountupdateencoding", $v_accountinfo['accountname'], $v_accountinfo['password'], array());
	//print_r($response);
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager&updenc=1");
	exit;
}


else
{
	if($variables->developeraccess >= 20)
	{
		$kols = array("#f6f7f8","#FFFFFF");
		if(isset($_GET['dir'])) $_GET['dir'] = urldecode($_GET['dir']);
		if($_GET['type'] == "serverlibrary")
		{
			if((!isset($_GET['dir']) || $_GET['dir'] == '') && (isset($_GET['duplicate']) || $_GET['type'] == 'serverlibrary'))
			{
				$basepath = realpath(__DIR__.'/../../../../../'.$_GET['type']);
				$filelist = ftp_ext_get_filelist('',"");
			} else {
				$basepath = $_GET['dir'];
				$filelist = ftp_ext_get_filelist('',$basepath);
				$folderlist = explode("/",str_replace($_GET['type'].'/','',$_GET['dir']));
				$numfolders = count($folderlist) - 1;
			}
			//print_r($filelist);
		} else {
			// local
			if((!isset($_GET['dir']) || $_GET['dir'] == '') && (!isset($_GET['duplicate']) || $_GET['duplicate'] == '') && $_GET['type'] != 'serverlibrary')
			{
				// Add module from other account
				$basepath = realpath(__DIR__.'/../../../../../'.$_GET['type']);
				$filelist = getFileList($basepath, false,0);

			}
			else if((!isset($_GET['dir']) || $_GET['dir'] == '') && (isset($_GET['duplicate']) || $_GET['type'] == 'serverlibrary'))
			{
				$basepath = realpath(__DIR__.'/../../../../../'.$_GET['type']);
				$filelist = getFileList($basepath, true,0);
			} else {
				$basepath = realpath(__DIR__.'/../../../../../'.$_GET['dir']);
				$filelist = getFileList($basepath, true,0);
				$folderlist = explode("/",str_replace($_GET['type'].'/','',$_GET['dir']));
				$numfolders = count($folderlist) - 1;
			}
		}
		//print 'serverpath = '.$serverpath.'<br>';
		//print 'basepath = '.$basepath.'<br>';
		//print_r($filelist);
		if(isset($_GET['duplicate']) && $_GET['duplicate'] == 1)
			$addtext = $formText_Duplicate_input;
		else
			$addtext = $formText_Add_inputSettings;
		if(isset($_GET['dir']) && $_GET['dir'] != '' && $_GET['type'] == 'accounts')
		{
			$copy_from_title = trim(str_replace('modules','',$_GET['dir']),'/');
		} else {
			$copy_from_title = trim($_GET['type'],'/');
		}
		$b_local = FALSE;
		$b_is_developer_account = FALSE;
		$o_query = $o_main->db->query('SELECT MAX(uniqueID) uniqueID FROM moduledata WHERE uniqueID < 10000');
		$v_max = $o_query ? $o_query->row_array() : array();
		$l_module_id_std = $l_next_module_id = intval($v_max['uniqueID']) + 1;
		$l_module_id_adv = 10000;
		
		if(isset($v_accountinfo['getynet_app_id']) && 0 < $v_accountinfo['getynet_app_id'])
		{
			$b_is_production_account = TRUE;
			if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
			$s_response = APIconnectAccount("account_info_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array());
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				if('dev' == strtolower($v_response['production_status']))
				{
					$b_is_developer_account = TRUE;
					$b_is_production_account = FALSE;
				}
			}
			
			if($b_is_production_account)
			{
				$l_next_module_id = 10000;
				$o_query = $o_main->db->query('SELECT MAX(uniqueID) uniqueID FROM moduledata');
				if($o_query && $o_query->num_rows()>0)
				{
					$v_max = $o_query->row_array();
					if($l_next_module_id <= $v_max['uniqueID'])
					{
						$l_module_id_adv = $l_next_module_id = intval($v_max['uniqueID']) + 1;
					}
				}
				$b_local = TRUE;
			}
		}
		?>
		<div class="module-manager">
		<div style="float:right;"><input type="button" class="btn btn-default btn-sm" name="back" value="<?php echo $formText_GoBack_input;?>" onClick="javascript:window.location.href='<?php echo substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module;?>';"></div>
		<?php
		if($_GET['type'] != 'serverlibrary' && strpos($basepath, $serverpath)===false)
		{
			print_info($formText_YouDoNotHaveAccessToThisFolder_input, $extradir.'/input/elementsInput/warning.jpg');
		} else {
			?>
			<form name="addmoduleform" id="addmoduleformid" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
			<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>" />
			<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>" />
			<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>" />
			<input type="hidden" name="caID" value="<?php echo $_GET['caID'];?>" />
			<input type="hidden" name="dir" id="dirfieldid" value="<?php echo $_GET['dir'];?>" />
			<input type="hidden" name="duplicate" value="<?php echo $_GET['duplicate'];?>" />
			<input type="hidden" name="module" value="<?php echo $_GET['module'];?>" />
			<input type="hidden" name="type" value="<?php echo $_GET['type'];?>" />
			<input type="hidden" name="includefile" value="addlibrary" />
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td colspan="5"><h2><?php echo $addtext.' '.$formText_moduleFrom_input.': '.ucfirst($copy_from_title);?></h2></td></tr>
			<tr><td colspan="5"><b>1. <?php echo $formText_WriteNewModuleName_input;?></b></td></tr>
			<tr><td class="fieldholder" colspan="5"><input type="text" name="newmodulename" value="<?php echo $_GET['newmodulename'];?>" /></td></tr>
			<tr><td colspan="5" style="padding-top:15px;"><b>2. <?php echo $formText_ChooseModule_input;?></b></td></tr>
			<?php
			$folderdir = $_GET['type'].'/';
			?>
			<tr>
				<td colspan="5">
					<a href="javascript:;" onClick="submitform('<?php echo ($_GET['type']!='accounts' ? $folderdir : '');?>','get'); return false;"><?php echo ucfirst($_GET['type']);?></a>
					<?php
					if($_GET['type'] != 'accounts')
					{
						for($x=0;$x<$numfolders;$x++)
						{
							$folderdir .= $folderlist[$x]."/";
							$is_account_folder = false;
							if(is_dir($folderdir.'modules') && is_file($folderdir.'/dbConnect.php')) $is_account_folder = true;
							?> -> <a href="javascript:;" onClick="submitform('<?php echo $folderdir.($is_account_folder?'modules':'');?>','get'); return false;"><?php echo str_replace('_folder','',$folderlist[$x]);?></a><?php
						}
					}
					?>
				</td>
			</tr>
			<tr><td height="10"></td></tr>
			<tr><td><?php echo $formText_ModuleName_input;?></td><td><?php echo $formText_InputVersion_input;?></td><td><?php echo $formText_ModuleVersion_input;?></td><td></td></tr><?php
			
			foreach($filelist as $folder)
			{
				$inputversion = '';
				$is_account_folder = false;
				$is_module_folder = false;
				if(is_dir($basepath.'/'.$folder['name'].'/modules') && is_file($basepath.'/'.$folder['name'].'/dbConnect.php')) $is_account_folder = true;
				if(!stristr($folder['name'],"_folder"))
				{
					if($_GET['type'] == 'serverlibrary')
					{
						$b_found_input = FALSE;
						$v_modules = ftp_ext_get_filelist('',$basepath.'/'.$folder['name']);
						foreach($v_modules as $v_item)
						{
							if(isset($v_item['dir']) && 'input' == $v_item['name']) $b_found_input = TRUE;
						}
						if($b_found_input)
						{
							$filelistversiontest = ftp_ext_get_filelist('',$basepath.'/'.$folder['name'].'/input');
							foreach($filelistversiontest as $fileversiontest)
							{
								if(strpos($fileversiontest['name'],".ver") > 0)
								{
									$inputversion = str_replace("_",".",substr($fileversiontest['name'],0,strpos($fileversiontest['name'],".ver")));
									break;
								}
							}
						}
					} else {
						if(is_dir($basepath.'/'.$folder['name'].'/input') && $vd = opendir($basepath.'/'.$folder['name'].'/input'))
						{
							while(($versionfile = readdir($vd)) !== false)
							{
								if(strpos($versionfile,".ver") > 0)
								{
									$inputversion = str_replace("_",".",substr($versionfile,0,strpos($versionfile,".ver")));
									break;
								}
							}
						}
					}
				}
				if($inputversion!='') $is_module_folder = true;
				
				?><tr class="module-item" bgcolor="<?php echo $kols[$counter % 2]; ?>">
					<td><?php
						if(!$is_account_folder && $is_module_folder)
						{
							?><b><?php echo $folder['name'];?></b><?php
						} else {
							?><a href="javascript:;" onClick="submitform('<?php echo str_replace($serverpath, '', $folder['dir']).($is_account_folder?'modules':'');?>','get'); return false;"><?php echo str_replace('_folder','',$folder['name']);?></a><?php
						}
					?></td>
					<td><?php echo $inputversion;?></td>
					<td></td>
					<td></td>
					<td style="width:120px;"><?php if(!$is_account_folder && $is_module_folder) { ?><a style="text-decoration:none; color:#000066;" href="javascript:;" onClick="submitform('<?php echo $folder['dir'];?>','config'); return false;"><?php echo $addtext.' '.$formText_module_input;?></a><?php } ?></td>
				</tr><?php
				$counter++;
			}
			
			
			?></table>
			<div id="mm-module-config" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title"><?php echo $formText_ModuleConfigration_Modulemanager;?></h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="module-config-mode"><?php echo $formText_Mode_Modulemanager;?></label>
								<select class="form-control" id="module-config-mode" name="module_config_mode">
									<?php
									$v_modulemodes = array("C"=>"Customer", "E"=>"Extra", "I"=>"Designer", "A"=>"Admin", "S"=>"System admin", "D"=>"Developer");
									foreach($v_modulemodes as $s_key => $s_mode_name)
									{
										if(strpos("IAS", $s_key) !== false && $v_moduledata['modulemode']!=$s_key) continue;
										?><option value="<?php echo $s_key;?>"<?php echo ((($b_is_developer_account || $b_is_production_account) && 'D' == $s_key) ? ' selected':'');?>><?php echo $s_mode_name;?></option><?php
									}
									?>
								</select>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" id="module-config-local" name="module_config_local" value="1"<?php echo ($b_local?' checked':'');?> data-std="<?php echo $l_module_id_std;?>" data-adv="<?php echo $l_module_id_adv;?>"> <?php echo $formText_LocalModule_Modulemanager;?>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="module_config_custom" value="1"> <?php echo $formText_CustomizedNeverUpdateFromLibrary_Modulemanager;?>
								</label>
							</div>
							<div class="form-group">
								<label for="module-config-module-id"><?php echo $formText_SuggestedModuleId_Modulemanager.' ('.$formText_NextRegular_Modulemanager.': '.$l_module_id_std.', '.$formText_NextLocal_Modulemanager.': '.$l_module_id_adv.')';?></label>
								<input type="text" class="form-control" id="module-config-module-id" name="module_config_module_id" value="<?php echo $l_next_module_id;?>">
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $formText_Close_Modulemanager;?></button>
							<button type="button" class="btn btn-primary" onClick="submitform('','post'); return false;"><?php echo $addtext.' '.$formText_module_input;?></button>
						</div>
					</div>
				</div>
			</div>
			</form>
			<script language="javascript" type="text/javascript">
			$(function(){
				$('#module-config-local').off('change').on('change', function(e){
					if($(this).is(':checked'))
					{
						$('#module-config-module-id.initial').val($(this).data('adv'));
					} else {
						$('#module-config-module-id.initial').val($(this).data('std'));
					}
				});
				$('#module-config-module-id').off('keypress').on('keypress', function(e){
					$(this).removeClass('initial');
				});
			});
			function submitform(dir, methodtype)
			{
				if(methodtype == 'config')
				{
					document.getElementById('dirfieldid').value = dir;
					if($('#module-config-module-id').not('.initial')) $('#module-config-module-id').addClass('initial');
					$('#mm-module-config').modal('show');
					return false;
				} else if(methodtype == 'post')
				{
					document.getElementById('addmoduleformid').action = "<?php echo $extradir;?>/addOn_include/<?php echo $_GET['includefile'];?>.php";
				} else {
					document.getElementById('dirfieldid').value = dir;
				}
				document.getElementById('addmoduleformid').method = methodtype;
				document.getElementById('addmoduleformid').submit();
				return false;
			}
			</script><?php
		}
		?></div><?php
	} else {
		?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField"><?php echo $formText_YouHaveNoAccessToThisModule_input;?></td></tr></table></div><?php
	}
}
function getFileList($dir, $recurse=false, $depth=false)
{
	# array to hold return value
	$retval = array();
	
	# add trailing slash if missing
	if(substr($dir, -1) != "/") $dir .= "/";
	//echo "dir = $dir<br />";
	$d = scandir($dir);
	natcasesort($d);
	foreach($d as $entry)
	{
		# skip hidden files
		//echo "entry = $entry<br />";
		if($entry[0] == ".") continue;
		if(is_dir("$dir$entry"))
		{
			$retval[] = array(
			"dir" => "$dir$entry/",
			"name" => "$entry"
			// "type" => filetype("$dir$entry"),
			//"size" => 0,
			//"lastmod" => filemtime("$dir$entry")
			);
			if($recurse && is_readable("$dir$entry/"))
			{
				if($depth === false)
				{
					$retval = array_merge($retval, getFileList("$dir$entry/", true));
				} elseif($depth > 0) {
					$retval = array_merge($retval, getFileList("$dir$entry/", true, $depth-1));
				}
			}
		} 
	}
	
	return $retval;
}	

function print_info($message, $image='')
{
	?><div><table width="100%" border="0">
		<tr><?php echo ($image!='' ? '<td><img src="'.$image.'" alt="" border="0" height="35" /></td>' : '');?><td><?php echo $message;?></td></tr>
	</table></div><?php
}
/*function get_resource()
{
	
}
function get_remote_dirlist($resource, $directory = '.')
{
	if (is_array($children = @ftp_rawlist($resource, $directory))) { 
		$items = array(); 

		foreach ($children as $child) { 
			$chunks = preg_split("/\s+/", $child); 
			//print_r($chunks);exit;
			list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks; 
			$item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file'; 
			array_splice($chunks, 0, 8); 
			$items[implode(" ", $chunks)] = $item; 
		} 

		return $items; 
	} 

	// Can add exeptions here
   	
}*/




function mm_remove_directory($s_dir)
{
	$l_i = 0;
	$b_return = TRUE;
	$s_delete_dir = dirname($s_dir);
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
			$b_return = FALSE;
		}
	}
	
	return $b_return;
}