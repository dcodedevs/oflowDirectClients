<?php
if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
if(!function_exists("include_local")) include(__DIR__."/../input/includes/fn_include_local.php");
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("ftp_file_put_content")) include(__DIR__."/../input/includes/ftp_commands.php");
if(!function_exists("get_folder_structure_checksum")) include(__DIR__."/fn_get_folder_structure_checksum.php");
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');

$s = array('_','.ver');
$r = array('.','');
$v_error_msg = array();
if(isset($_GET['addobject']))
{
	$s_title = $s_lib_version_path = $s_lib_path = $s_local_path = "";
	
	$v_r = array("/",".");
	if($_GET['addobject'] == "fw")
	{
		$s_element = 'fw';
		$s_local_path = realpath(__DIR__."/../../../fw/");
		$s_title = $formText_CreateNewVersionFor_input.': '.$s_element;
		
		$s_lib_category = "";
		if(is_file($s_local_path."/central.lib")) $s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
		if($s_lib_category == "") $s_lib_category = 'frameworks';
		$v_central_lib_conf = explode('/', $s_lib_category);
		if(count($v_central_lib_conf)>1)
		{
			if($v_central_lib_conf[1] != $s_element) $s_title .= ' ('.$v_central_lib_conf[1].')';
			$s_lib_category = $v_central_lib_conf[0];
			$s_element = $v_central_lib_conf[1];
		}
		$s_lib_version_path = "/".$s_lib_category."/".$s_element;
	}
	if($_GET['addobject'] == "account_elements")
	{
		$s_element = str_replace($v_r,"",$_GET[$_GET['addobject']]);
		$s_local_path = realpath(__DIR__."/../../../".$s_element."/");
		$s_title = $formText_CreateNewVersionFor_input.": ".$s_element;
		
		$s_lib_category = "";
		if(is_file($s_local_path."/central.lib")) $s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
		if($s_lib_category == "") $s_lib_category = 'account_elements';
		$v_central_lib_conf = explode('/', $s_lib_category);
		if(count($v_central_lib_conf)>1)
		{
			if($v_central_lib_conf[1] != $s_element) $s_title .= ' ('.$v_central_lib_conf[1].')';
			$s_lib_category = $v_central_lib_conf[0];
			$s_element = $v_central_lib_conf[1];
		}
		$s_lib_version_path = "/".$s_lib_category."/".$s_element;
	}
	if($_GET['addobject'] == "sys_module")
	{
		$s_element = str_replace($v_r,"",$_GET[$_GET['addobject']]);
		$s_local_path = realpath(__DIR__."/../../".$s_element);
		$s_title = $formText_CreateNewVersionFor_input.": ".$s_element;
		
		$s_lib_category = "";
		if(is_file($s_local_path."/central.lib")) $s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
		if($s_lib_category == "") $s_lib_category = "modules";
		$v_central_lib_conf = explode('/', $s_lib_category);
		if(count($v_central_lib_conf)>1)
		{
			if($v_central_lib_conf[1] != $s_element) $s_title .= ' ('.$v_central_lib_conf[1].')';
			$s_lib_category = $v_central_lib_conf[0];
			$s_element = $v_central_lib_conf[1];
		}
		$s_lib_version_path = "/".$s_lib_category."/".$s_element;
	}
}

// Verify is library server synced
$b_server_is_synced = FALSE;
$data = array('data'=>json_encode(array("action"=>"is_server_synced", "server"=>'localhost')));
//call api
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$s_response = curl_exec($ch);
curl_close($ch);

$v_response = json_decode($s_response,true);
if(isset($v_response['status']) && 1 == $v_response['status'])
{
	$v_server = array(
		'api_token' => $v_response['api_token'],
		'api_url' => $v_response['api_url']
	);
	if(isset($v_response['synced']) && 1 == $v_response['synced'])
	{
		$b_server_is_synced = TRUE;
	}
}

// Get locked elements
$v_locked_elements = array();
$data = array('data'=>json_encode(array('action'=>'get_locked_elements')));
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$v_data = json_decode($response,true);
if(isset($v_data['status']) && $v_data['status'] == 1)
{
	foreach($v_data['items'] as $v_item)
	{
		$v_locked_elements[$v_item['category'].'_'.$v_item['folder']] = $v_item;
	}
}

if(isset($_POST['move_to_lib']) && 1 == $_POST['move_to_lib'])
{
	$v_return = array(
		'status' => 0,
		'messages' => array(),
	);
	if($b_server_is_synced)
	{
		$uploadPath = "/uploads/";
		$v_excludes = array();
		$dateString = date("Y_m_d_H_i_");
		/*if($moduleName == "all")
		{
			$sourcePath = $path;
			$moduleName = $accountname;
			$v_scan = scandir($path."/backup");
			foreach($v_scan as $s_item) if($s_item != "." && $s_item != "..") $v_excludes[] = $path."/backup/".$s_item;
			$v_compare = array("protected", "storage", ".htaccess", "index.php");
			$v_scan = scandir($path."/uploads");
			foreach($v_scan as $s_item)
			{
				if($s_item != "." && $s_item != ".." && !in_array($s_item, $v_compare)) $v_excludes[] = $path."/uploads/".$s_item;
			}
			$v_compare = array(".htaccess", "index.php");
			$v_scan = scandir($path."/uploads/protected");
			foreach($v_scan as $s_item) if($s_item != "." && $s_item != ".." && !in_array($s_item, $v_compare)) $v_excludes[] = $path."/uploads/protected/".$s_item;
			$v_scan = scandir($path."/uploads/storage");
			foreach($v_scan as $s_item) if($s_item != "." && $s_item != ".." && !in_array($s_item, $v_compare)) $v_excludes[] = $path."/uploads/storage/".$s_item;
			$v_scan_modules = scandir($path."/modules");
			foreach($v_scan_modules as $s_item)
			{
				$b_reduced_module = FALSE;
				if($s_item == "." && $s_item == "..") continue;
				$v_scan = scandir($path.'/modules/'.$s_item.'/input/settings/tables');
				foreach($v_scan as $s_setting)
				{
					if($s_setting == "." && $s_setting == "..") continue;
					$v_table = include_local($path.'/modules/'.$s_item.'/input/settings/tables/'.$s_setting);
					if(isset($v_table['moduleLibraryType']) && $v_table['moduleLibraryType'] == 2) $b_reduced_module = TRUE; //Reduced
				}
				if($b_reduced_module)
				{
					$v_compare = array(
						"APIconnect.php",
						"buttons.php",
						"class_DatabaseTable.php",
						"config_module.php",
						"databaseFieldsCheck.php",
						"fieldloader.php",
						"fn_check_table_system_fields.php",
						"fn_get_developer_access.php",
						"fn_get_table_indexes.php",
						"fn_get_table_indexes_from_db.php",
						"fn_include_local.php",
						"fnctn_devide_by_upercase.php",
						"fnctn_find_related_modules.php",
						"fnctn_get_curent_GET_params.php",
						"fnctn_get_files.php",
						"fnctn_get_form_text_variables.php",
						"fnctn_get_language_variables.php",
						"fnctn_get_table_fields.php",
						"fnctn_get_table_fields_from_db.php",
						"fnctn_get_table_fields_structure.php",
						"fnctn_get_tables.php",
						"ftp_commands.php",
						"moduleinfo.php",
						"moduleinit.php",
						"readInputLanguage.php"
					);
					$v_scan = scandir($path.'/modules/'.$s_item.'/input/includes');
					foreach($v_scan as $s_item2) if($s_item2 != "." && $s_item2 != ".." && !in_array($s_item2, $v_compare)) $v_excludes[] = $path.'/modules/'.$s_item.'/input/includes/'.$s_item2;
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/buttontypes';
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/fieldtypes';
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/include_safe';
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/contentreg.php';
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/delete.php';
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/update.php';
					$v_excludes[] = $path.'/modules/'.$s_item.'/input/versionlog.txt';
				}
			}
		}*/
		$newFolderName = $dateString . $s_element;
		$v_return['upload_object'] = $newFolderName;
		$destinationPath = $uploadPath.$newFolderName."/".$s_element;
		if(ftp_ext_copy_directory($s_local_path, $destinationPath, true, "put", $v_excludes))
		{
			ftp_ext_chmod_directory($uploadPath.$newFolderName, "775");
			
			//verify copied object checksum
			$s_local_folder_checksum = get_folder_structure_checksum($s_local_path, TRUE, FALSE, $v_excludes);
			$v_param = array(
				'auth_token' => $v_server['api_token'],
				'action' => 'verify_upload_checksum',
				'object' => $newFolderName."/".$s_element,
				'checksum' => $s_local_folder_checksum,
				'excludes' => $v_excludes,
			);
			$s_param = json_encode($v_param);
			$o_curl = curl_init($v_server['api_url']);
			curl_setopt($o_curl, CURLOPT_HEADER, 0);
			curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($o_curl, CURLOPT_POST,           TRUE);
			curl_setopt($o_curl, CURLOPT_POSTFIELDS,     $s_param); 
			$s_response = curl_exec($o_curl);
			curl_close($o_curl);
			$v_response = json_decode($s_response, TRUE);
			
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				$v_return['status'] = 1;
				$v_return['checksum'] = $s_local_folder_checksum;
			} else {
				$v_return['messages'][] = 'LIB_API_VERIFY_CHECKSUM: '.$newFolderName."/".$s_element.$v_response['message'];
			}
		}
	} else {
		$v_return['messages'][] = $formText_LocalLibraryIsNotSynchronized_Output;
	}
	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($v_return);
	exit;
}

if(isset($_POST['handle_version']) && 1 == $_POST['handle_version'])
{
	set_time_limit(800);
	$v_return = array(
		'status' => 0,
		'messages' => array(),
	);
	if($b_server_is_synced)
	{
		$v_excludes = array();
		$v_param = array
		(
			'action'=>'handle_object_version',
			'category'=>$s_lib_category,
			'element'=>$s_element,
			'username'=>$_COOKIE['username'],
			'version_number'=>$_POST['version_number'],
			'upload_object'=>$_POST['upload_object'],
		);
		$v_param = array('data'=>json_encode($v_param));
		$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
		$o_curl = curl_init($url);
		curl_setopt($o_curl, CURLOPT_POST, 1);
		curl_setopt($o_curl, CURLOPT_POSTFIELDS, http_build_query($v_param));
		curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
		$s_response = curl_exec($o_curl);
		curl_close($o_curl);
		
		$v_response = json_decode($s_response,true);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$s_relative_local_path = str_replace(realpath(__DIR__.'/../../../'), '', $s_local_path);
			$v_items = scandir($s_local_path);
			foreach($v_items as $s_item)
			{
				if($s_item != "" && $s_item[0] != "." && substr($s_item,-4)==".ver")
				{
					ftp_delete_file($s_relative_local_path."/".$s_item);
				}
			}
			ftp_file_put_content($s_relative_local_path."/".$_POST['version_number'].".ver", $_POST['version_number']);
			if(isset($v_response['upgrade_script']))
			{
				ftp_file_put_content($s_relative_local_path."/upgrade_script.sh", $v_response['upgrade_script']);
			}
			if(isset($v_response['auto_lib_element']))
			{
				ftp_file_put_content($s_relative_local_path."/central.lib", $v_response['auto_lib_element']);
			}
			$s_checksum = get_folder_structure_checksum($s_local_path, TRUE, FALSE, $v_excludes);
			if($v_response['checksum'] == $s_checksum)
			{
				$v_return['status'] = 1;
			} else {
				$v_return['messages'][] = $s_local_path.':'.$s_checksum.':'.$s_response.':'.$formText_ChecksumDoesNotMatchForCreatedVersion_input;
			}
		} else {
			$v_return['messages'][] = 'LIB_API_HANDLE_VERSION: '.$v_response['message'];
		}
	} else {
		$v_return['messages'][] = $formText_LocalLibraryIsNotSynchronized_Output;
	}
	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($v_return);
	exit;
}

if($b_server_is_synced && isset($_POST['approve_version']) && 1 == $_POST['approve_version'])
{
	$v_return = array(
		'status' => 0,
		'messages' => array(),
	);
	if($b_server_is_synced)
	{
		$v_param = array
		(
			'action'=>'approve_object_version',
			'category'=>$s_lib_category,
			'element'=>$s_element,
			'username'=>$_COOKIE['username'],
			'version_number'=>$_POST['version_number'],
		);
		$v_param = array('data'=>json_encode($v_param));
		$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
		$o_curl = curl_init($url);
		curl_setopt($o_curl, CURLOPT_POST, 1);
		curl_setopt($o_curl, CURLOPT_POSTFIELDS, http_build_query($v_param));
		curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
		$s_response = curl_exec($o_curl);
		curl_close($o_curl);
		
		$v_response = json_decode($s_response,true);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$v_return['status'] = 1;
		} else {
			$v_return['messages'][] = 'LIB_API_APPROVE_VERSION: '.$v_response['message'];
		}
	} else {
		$v_return['messages'][] = $formText_LocalLibraryIsNotSynchronized_Output;
	}
	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($v_return);
	exit;
}

if(isset($_POST['set_description']) && 1 == $_POST['set_description'])
{
	
	if(isset($_POST['connected_tasks']) && 0 < sizeof($_POST['connected_tasks']))
	{
		$v_param = array(
			'api_url' => 'https://s24.getynet.com/accounts/dcodeCrm8No/api/',
			'module' => 'Totaloverview6',
			'action' => 'productelement_update_task_version',
			'library_element_id' => $_POST['element_id'],
			'task_id' => $_POST['connected_tasks'],
			'version_number' => $_POST['version_number'],
		);
		$v_response = fw_api_call($v_param, TRUE);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{} else {
			$v_return['messages'][] = 'Error occurred connecting version to tasks: '.json_encode($v_response['message']);
		}
	}
	
	$v_return = array(
		'status' => 0,
		'messages' => array(),
	);
	$v_param = array
	(
		'action'=>'update_object_version',
		'category'=>$s_lib_category,
		'element'=>$s_element,
		'username'=>$_COOKIE['username'],
		'version_number'=>$_POST['version_number'],
		'description'=>$_POST['description'],
	);
	$v_param = array('data'=>json_encode($v_param));
	$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
	$o_curl = curl_init($url);
	curl_setopt($o_curl, CURLOPT_POST, 1);
	curl_setopt($o_curl, CURLOPT_POSTFIELDS, http_build_query($v_param));
	curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
	$s_response = curl_exec($o_curl);
	curl_close($o_curl);
	
	$v_response = json_decode($s_response,true);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$v_return['status'] = 1;
		
		if(isset($_POST['unlock']) && 1 == $_POST['unlock'])
		{
			$v_data = array
			(
				'action'=>'unlock_element',
				'category'=>$s_lib_category,
				'element'=>$s_element,
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
		}
	} else {
		$v_return['messages'][] = 'LIB_API_UPDATE_VERSION: '.$v_response['message'];
	}
	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($v_return);
	exit;
}


if(isset($_POST['rollback_version']) && 1 == $_POST['rollback_version'])
{
	$v_return = array(
		'status' => 0,
		'messages' => array(),
	);
	$v_param = array
	(
		'action'=>'rollback_object_version',
		'category'=>$s_lib_category,
		'element'=>$s_element,
		'username'=>$_COOKIE['username'],
		'version_number'=>$_POST['version_number'],
		'upload_object'=>$_POST['upload_object'],
	);
	$v_param = array('data'=>json_encode($v_param));
	$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
	$o_curl = curl_init($url);
	curl_setopt($o_curl, CURLOPT_POST, 1);
	curl_setopt($o_curl, CURLOPT_POSTFIELDS, http_build_query($v_param));
	curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
	$s_response = curl_exec($o_curl);
	curl_close($o_curl);
	
	$v_response = json_decode($s_response,true);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$v_return['status'] = 1;
	} else {
		$v_return['messages'][] = 'LIB_API_ROLLBACK_VERSION: '.$v_response['message'];
	}
	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($v_return);
	exit;
}

if(isset($_GET['instance']) && '' != $_GET['instance'] && $_SESSION['mm_add_object_instance'] == $_GET['instance'])
{
	if(!empty($s_lib_category) && !empty($s_element))
	{
		$v_param = array
		(
			'action'=>'add_object_version',
			'category'=>$s_lib_category,
			'element'=>$s_element,
			'username'=>$_COOKIE['username'],
			'script_version'=>'8_200',
			//'rollback'=>1,
		);
		$v_param = array('data'=>json_encode($v_param));
		$s_url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
		$o_curl = curl_init($s_url);
		curl_setopt($o_curl, CURLOPT_POST, 1);
		curl_setopt($o_curl, CURLOPT_POSTFIELDS, http_build_query($v_param));
		curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, TRUE);
		$s_response = curl_exec($o_curl);
		curl_close($o_curl);
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$s_version_number = $v_response['version'];
			$l_element_id = $v_response['element_id'];
			$s_lib_path = "/".$s_lib_category."/".$s_element."/".$s_version_number."/".$s_element;
		} else {
			$v_error_msg[] = 'LIB_API_INIT_VERSION: '.$v_response['message'];
		}
	} else {
		$v_error_msg[] = $formText_WrongParameters_input;
	}
} else {
	$v_error_msg[] = $formText_YouHaveEnteredWrongWayInThisPageOrTokenExpiredByTimeOrParallelWindowsStartProcessAgainFromBegining_input;
}

$v_tasks = array();
if(0 < $l_element_id)
{
	$v_param = array(
		'api_url' => 'https://s24.getynet.com/accounts/dcodeCrm8No/api/',
		'module' => 'Totaloverview6',
		'action' => 'productelement_task_list_get',
		'library_element_id' => $l_element_id,
	);
	$v_response = fw_api_call($v_param, TRUE);
	if(isset($v_response['status']) && 1 == $v_response['status'])
	{
		$v_tasks = $v_response['items'];
	}
}
$v_status = array(
	'' => $formText_Inactive_Modulemanager,
	'0' => $formText_Inactive_Modulemanager,
	'1' => $formText_Active_Modulemanager,
	'2' => $formText_Completed_Modulemanager,
);
?>
<div class="module-manager">
	<h1><?php echo $s_title;?></h1>
	<div id="mm_output_messages">
		<?php
		if(sizeof($v_error_msg)>0)
		{
			foreach($v_error_msg as $s_msg)
			{
				?><div class="alert alert-danger"><?php echo $s_msg;?></div><?php
			}
		} else {
			?>
			<div class="well well-sm"><?php echo $formText_StartNewVersionCreation_input;?></div>
			<div class="well well-sm"><?php echo $formText_NewVersionNumber_input.': '.$s_version_number;?></div>
			<div class="well well-sm"><?php echo $formText_MoveObjectToLibrary_input;?></div>
			<?php
		}
		?>
	</div>
	<div class="form-group input-description hide">
		<label><?php echo $formText_Description_Output;?></label>
		<textarea class="form-control" id="input-description" placeholder="<?php echo $formText_EnterVersionDescriptionAndPressSave_Output;?>"></textarea>
	</div>
	<div class="input-task hide">
		<label><?php echo $formText_ConnectTask_Output;?></label>
		<table class="table table-condensed table-hover">
		<thead>
		<tr>
			<th></th>
			<th><?php echo $formText_Status_Output;?></th>
			<th><?php echo $formText_Responsible_Output;?></th>
			<th><?php echo $formText_Name_Output;?></th>
			<th><?php echo $formText_Project_Output;?></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
			<?php
			foreach($v_tasks as $v_task)
			{
				?><tr>
					<td><input type="checkbox" name="connect_task[]" value="<?php echo $v_task['id'];?>"></td>
					<td><?php echo $v_status[$v_task['taskStatus']];?></td>
					<td><?php echo $v_task['employeeName'];?></td>
					<td><?php echo $v_task['taskName'];?></td>
					<td><?php echo $v_task['projectName'];?></td>
					<td>
						<span class="glyphicon glyphicon-info-sign hoverEyeCreated">
							<div class="hoverInfo">
								<div><?php echo $formText_ProductName_Output.': '.$v_task['productName'];?></div>
								<div><?php echo $formText_CustomerName_Output.': '.$v_task['customerName'];?></div>
							</div>
						</span>
					</td>
				</tr><?php
			}
			?>
		</tbody>
		</table>
	</div>
	<div style="margin-top:15px;">
		<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=verify_object&verifyobject=".$_GET['addobject'].(isset($_GET[$_GET["addobject"]]) ? "&".$_GET["addobject"]."=".$_GET[$_GET["addobject"]] : "")."&_=".rand(10000,99999);?>" class="btn btn-default btn-go-back optimize hide"><?php echo $formText_GoBack_input;?></a>
		<?php if('agris@123onweb.no' == $variables->loggID) { ?>
		<button type="button" class="btn btn-danger btn-abort"><?php echo $formText_Abort_input;?></button>
		<?php } ?>
		<button type="button" class="btn btn-default btn-done hide"><?php echo $formText_Done_input;?></button>
		<?php
		$s_key = $s_lib_category.'_'.$s_element;
		if(isset($v_locked_elements[$s_key]))
		{
			?><button type="button" class="btn btn-default btn-done unlock hide"><?php echo $formText_CompleteAndUnlock_input;?></button><?php
		}
		?>
	</div>
</div>
<script type="text/javascript">
var mm_abort = false;
var mm_completed = false;
var mm_upload_object;
$(function(){
	<?php if(sizeof($v_error_msg)==0) { ?>
	$('#mm_output_messages').append('<div class="mm_loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
	setTimeout(mm_move_to_library, 800);
	<?php } else { ?>
	$('.btn-go-back').removeClass('hide');
	$('.btn-abort').addClass('hide');
	<?php } ?>
	$('button.btn-done').off('click').on('click', function(e){
		e.preventDefault();
		mm_update_description($(this).is('.unlock'));
	});
	$('button.btn-abort').off('click').on('click', function(e){
		e.preventDefault();
		mm_abort_version();
	});
});
function mm_move_to_library()
{
	if(mm_abort)
	{
		mm_rollback_version();
		return;
	}
	mm_ajax_call(
		'<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=add_object&addobject=".$_GET["addobject"].(isset($_GET[$_GET["addobject"]]) ? "&".$_GET["addobject"]."=".$_GET[$_GET["addobject"]] : "");?>',
		{ move_to_lib: 1 },
		function(json){
			if(1 == json.status)
			{
				mm_upload_object = json.upload_object;
				mm_add_message('success', '<?php echo $formText_ObjectFilesSuccessfullyMovedToLibrary_input;?>');
				mm_handle_version_in_library();
			} else {
				$.each(json.messages, function(index, value){
					mm_add_message('danger', value);
				});
				mm_rollback_version(json.upload_object);
			}
		},
		function(){
			mm_add_message('danger', '<?php echo $formText_ErrorOccurredMovingObjectFiles_input;?>');
			mm_rollback_version();
		}
	);
}

function mm_handle_version_in_library()
{
	if(mm_abort)
	{
		mm_rollback_version(mm_upload_object);
		return;
	}
	mm_add_message('well', '<?php echo $formText_HandleObjectVersionInLibrary_input;?>');
	$('#mm_output_messages').append('<div class="mm_loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
	mm_ajax_call(
		'<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=add_object&addobject=".$_GET["addobject"].(isset($_GET[$_GET["addobject"]]) ? "&".$_GET["addobject"]."=".$_GET[$_GET["addobject"]] : "");?>',
		{ handle_version: 1, upload_object: mm_upload_object, version_number: '<?php echo $s_version_number;?>' },
		function(json){
			if(1 == json.status)
			{
				mm_add_message('success', '<?php echo $formText_VersionHandledSuccessfully_input;?>');
				mm_approve_version_in_library();
			} else {
				$.each(json.messages, function(index, value){
					mm_add_message('danger', value);
				});
				mm_rollback_version(mm_upload_object);
			}
		},
		function(){
			mm_add_message('danger', '<?php echo $formText_ErrorOccurredHandlingVersion_input;?>');
			mm_rollback_version(mm_upload_object);
		}
	);
}

function mm_approve_version_in_library()
{
	if(mm_abort)
	{
		mm_rollback_version(mm_upload_object);
		return;
	}
	mm_add_message('well', '<?php echo $formText_ApproveVersionStatusInLibrary_input;?>');
	$('#mm_output_messages').append('<div class="mm_loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
	mm_ajax_call(
		'<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=add_object&addobject=".$_GET["addobject"].(isset($_GET[$_GET["addobject"]]) ? "&".$_GET["addobject"]."=".$_GET[$_GET["addobject"]] : "");?>',
		{ approve_version: 1, version_number: '<?php echo $s_version_number;?>' },
		function(json){
			if(1 == json.status)
			{
				mm_completed = true;
				mm_add_message('success', '<?php echo $formText_VersionApprovedSuccessfully_input;?>');
				$('.btn-done, .input-description, .input-task').removeClass('hide');
			} else {
				$.each(json.messages, function(index, value){
					mm_add_message('danger', value);
				});
				mm_rollback_version(mm_upload_object);
			}
		},
		function(){
			mm_add_message('danger', '<?php echo $formText_ErrorOccurredApprovingVersion_input;?>');
			mm_rollback_version(mm_upload_object);
		}
	);
}

function mm_update_description(unlock)
{
	mm_add_message('well', '<?php echo $formText_SetVersionDescriptionInLibrary_input;?>');
	$('#mm_output_messages').append('<div class="mm_loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
	var connected_tasks = $(".input-task input:checkbox:checked").map(function(){
      return $(this).val();
    }).get();
	mm_ajax_call(
		'<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=add_object&addobject=".$_GET["addobject"].(isset($_GET[$_GET["addobject"]]) ? "&".$_GET["addobject"]."=".$_GET[$_GET["addobject"]] : "");?>',
		{ set_description: 1, element_id: '<?php echo $l_element_id;?>', version_number: '<?php echo $s_version_number;?>', description: $('#input-description').val(), connected_tasks: connected_tasks, unlock: (unlock ? 1 : 0) },
		function(json){
			if(1 == json.status)
			{
				mm_add_message('success', '<?php echo $formText_Done_input;?>');
				var _a = $('<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="optimize"></a>');
				$('#mm_output_messages').append(_a);
				_a.trigger('click');
			} else {
				$.each(json.messages, function(index, value){
					mm_add_message('danger', value);
				});
			}
		},
		function(){
			mm_add_message('danger', '<?php echo $formText_ErrorOccurredSavingDescription_input;?>');
		}
	);
}

function mm_rollback_version(upload_object)
{
	if(upload_object === undefined) upload_object = '';
	mm_add_message('well', '<?php echo $formText_RollbackStarted_input;?>');
	$('#mm_output_messages').append('<div class="mm_loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
	mm_ajax_call(
		'<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=add_object&addobject=".$_GET["addobject"].(isset($_GET[$_GET["addobject"]]) ? "&".$_GET["addobject"]."=".$_GET[$_GET["addobject"]] : "");?>',
		{ rollback_version: 1, upload_object: upload_object, version_number: '<?php echo $s_version_number;?>' },
		function(json){
			if(1 == json.status)
			{
				mm_add_message('success', '<?php echo $formText_RollbackCompletedSuccessfully_input;?>');
			} else {
				$.each(json.messages, function(index, value){
					mm_add_message('danger', value);
				});
			}
			$('.btn-go-back').removeClass('hide');
			$('.btn-done, .btn-abort, .input-description, .input-task').addClass('hide');
		},
		function(){
			mm_add_message('danger', '<?php echo $formText_ErrorOccurredPerformingRollback_input;?>');
			$('.btn-go-back').removeClass('hide');
			$('.btn-done, .btn-abort, .input-description, .input-task').addClass('hide');
		}
	);
}

<?php if('agris@123onweb.no' == $variables->loggID) { ?>
function mm_abort_version()
{
	mm_add_message('danger', '<?php echo $formText_Aborting_input;?>');
	$('#mm_output_messages').append('<div class="mm_loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');
	if(mm_completed)
	{
		mm_rollback_version(mm_upload_object);
	} else {
		mm_abort = true;
	}
		
}
<?php } ?>

function mm_add_message(type, message)
{
	$('#mm_output_messages .mm_loader').remove();
	if('well' == type)
	{
		$('#mm_output_messages').append('<div class="well well-sm">' + message + '</div>');
	} else {
		$('#mm_output_messages').append('<div role="alert" class="alert alert-' + type + '">' + message + '</div>');
	}
}
function mm_ajax_call(url, data, success_callback, fail_callback)
{
	if(url == "") return;
	// data object check
	if (typeof(data) !== 'object') var data = {};
	// success_callback check
	if (typeof(success_callback) !== 'function') var success_callback = function() { };
	// fail_callback check
	if (typeof(fail_callback) !== 'function') var fail_callback = function() { };
	
	// Default data
	var __data = {
		fwajax: 1,
		fw_nocss: 1
	}

	// Concat default and user data
	var ajaxData = $.extend({}, __data, data);

	// Run AJAX
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: url,
		data: ajaxData,
		success: function(json){
			success_callback(json);
		}
	}).fail(function(){
		fail_callback();
	});
}
</script>
<style type="text/css">
#mm_output_messages > div {
	margin-bottom:5px;
}
#mm_output_messages .mm_loader {
	text-align:center;
}
.lds-ellipsis {
  display: inline-block;
  position: relative;
  width: 64px;
  height: 64px;
}
.lds-ellipsis div {
  position: absolute;
  top: 27px;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: #666666;
  animation-timing-function: cubic-bezier(0, 1, 1, 0);
}
.lds-ellipsis div:nth-child(1) {
  left: 6px;
  animation: lds-ellipsis1 0.6s infinite;
}
.lds-ellipsis div:nth-child(2) {
  left: 6px;
  animation: lds-ellipsis2 0.6s infinite;
}
.lds-ellipsis div:nth-child(3) {
  left: 26px;
  animation: lds-ellipsis2 0.6s infinite;
}
.lds-ellipsis div:nth-child(4) {
  left: 45px;
  animation: lds-ellipsis3 0.6s infinite;
}
.hoverEye {
	position: relative;
	color: #0284C9;
	float: right;
	margin-top: 2px;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:450px;
	display: none;
	color: #000;
	position: absolute;
	right: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEye:hover .hoverInfo {
	display: block;
}
.hoverEyeCreated {
	position: relative;
	color: #cecece;
	float: left;
	margin-top: 2px;
}
.hoverEyeCreated .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:250px;
	display: none;
	color: #000;
	position: absolute;
	left: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEyeCreated:hover .hoverInfo {
	display: block;
}
@keyframes lds-ellipsis1 {
  0% {
    transform: scale(0);
  }
  100% {
    transform: scale(1);
  }
}
@keyframes lds-ellipsis3 {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(0);
  }
}
@keyframes lds-ellipsis2 {
  0% {
    transform: translate(0, 0);
  }
  100% {
    transform: translate(19px, 0);
  }
}
</style>