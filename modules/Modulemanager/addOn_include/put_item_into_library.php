<?php
if(is_file(__DIR__."/../../../ftpConnect.php")) include(__DIR__."/../../../ftpConnect.php");
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("include_local")) include(__DIR__."/../input/includes/fn_include_local.php");
if(!function_exists("get_folder_structure_checksum")) include(__DIR__."/fn_get_folder_structure_checksum.php");
$path = realpath(__DIR__."/../../../");
$uploadPath = "/uploads/";
$moduleList = array();

$v_exclude_items = array(
	array(
		'folder' => 'elementsCustomized',
		'label' => $formText_elementsCustomized_Modulemanager,
		'checked' => 1,
	),
);

// Verify is library slave/main server synced
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

$getPath = "";
if(isset($_GET['path']))
{
	$getPath = str_replace(array("../","./"), "", $_GET['path']);
	$path .= $getPath;
}
$folders = array('..', '.');
if($getPath == "")
{
	$files = array();
	$v_items = ftp_get_filelist(str_replace(realpath(__DIR__."/../../../"), "", $path));
	
	foreach($v_items as $v_item)
	{
		if(isset($v_item["dir"]))
		{
			$files[] = $v_item["name"];
		}
	}
} else {
	$files = array_diff(scandir(realpath($path)), $folders);
}
foreach($files as $file)
{
	if(is_dir($path."/".$file))
	{
		array_push($moduleList, $file);
	}
}
if(isset($_POST['submit']))
{
	$moduleName = $_POST['folder'];
	if($moduleName != "")
	{
		$v_excludes = array();
		$dateString = date("Y_m_d_H_i_");
		if($moduleName == "all")
		{
			foreach($v_exclude_items as $l_key => $v_item) ${'b_'.$v_item['folder']} = (isset($_POST['exclude_folder_'.$l_key]) && $_POST['exclude_folder_'.$l_key] == $v_item['folder']);
			
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
			if($b_elementsCustomized)
			{
				$v_excludes[] = $path."/elementsCustomized";
			}
			
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
		} else {
			$sourcePath = $path."/".$moduleName;
		}
		$b_connect_to_main_library = TRUE;
		$newFolderName = $dateString . $moduleName;
		$destinationPath = $uploadPath.$newFolderName."/".$moduleName;
		if(ftp_ext_copy_directory($sourcePath, $destinationPath, true, "put", $v_excludes))
		{
			ftp_ext_chmod_directory($uploadPath.$newFolderName, "775");
			
			//verify copied object checksum
			$s_local_folder_checksum = get_folder_structure_checksum($sourcePath, TRUE, FALSE, $v_excludes);
			$v_param = array(
				'auth_token' => $v_server['api_token'],
				'action' => 'verify_upload_checksum',
				'object' => $newFolderName."/".$moduleName,
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
			/*
			$v_data = array
			(
				'action'=>'verify_object_checksum',
				'path'=>$destinationPath,
				'checksum'=>$s_local_folder_checksum,
				'excludes'=>array()
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
			if(isset($v_data['status']) && $v_data['status'] == 1)*/
			{
				?><div role="alert" class="alert alert-success">
					<strong>Well done!</strong> Folder has been coppied to library (Checksum: <?php echo $s_local_folder_checksum;?>)
				</div><?php
			} else {
				?><div role="alert" class="alert alert-danger">
					<strong>Fail!</strong> Folder has been coppied to library partly (API: <?php echo $v_data['message'];?>)
				</div><?php
			}
		}
	}
}
$linkQuery = $_SERVER['QUERY_STRING'];
$queryArray = explode("&", $linkQuery);
$resultQueryArray = array();
foreach($queryArray as $queryString)
{
	$queryStringArray = explode("=", $queryString);
	if($queryStringArray[0] != "path"){
		array_push($resultQueryArray, $queryString);
	}
}
$linkQuery = implode("&", $resultQueryArray);
?>
<div class="breadcrumb">
	<a href="index.php?<?php echo $linkQuery;?>">Root folder</a>

	<?php
	if($getPath != ""){
		$pathArray = explode("/", $getPath);
		$pathCount = count($pathArray);
		if($pathCount > 1){
			$resultPathString = "";
			foreach($pathArray as $pathString){
				if($pathString != ""){
					$resultPathString .= "/".$pathString;
					?>
					> <a href="index.php?<?php echo $linkQuery;?>&path=<?php echo $resultPathString?>"><?php echo $pathString;?></a>
					<?php
				}
			}
		}
	}
	?>
</div>
<div class="heading">Select folder to copy to library</div>
<form action="" method="post">
	<?php if($getPath == ""){ ?>
		<input type="radio" name="folder" value="all" id="allAccount"/>
		<label for="allAccount">Entire account</label>
	<?php } ?>
	<?php
	foreach($moduleList as $singleModule)
	{
		?>
		<div class="checkboxRow">
			<input type="radio" name="folder" value="<?php echo $singleModule?>" id="<?php echo $singleModule?>"/>
			<?php
			$files = array_diff(scandir($path."/".$singleModule), $folders);
			$link = false;
			foreach($files as $file){
				if(is_dir($path."/".$singleModule."/".$file)){
					$link = true;
					break;
				}
			}
			if($link)
			{
				?>
				<label>
					<a href="index.php?<?php echo $linkQuery;?>&path=<?php echo $getPath?>/<?php echo $singleModule;?>">
						<?php echo $singleModule?>
					</a>
				</label>
				<?php
			}else{
				?>
				<label for="<?php echo $singleModule?>">
					<?php echo $singleModule?>
				</label>
				<?php
			}
			?>
		</div>
		<?php
	}
	if($getPath == "")
	{
		?>
		<div id="mm-exlude-folders" class="panel panel-default" style="margin-top:20px;">
			<div class="panel-heading"><?php echo $formText_ExcludeFolders_Modulemanager;?></div>
			<div class="panel-body">
			<?php
			foreach($v_exclude_items as $l_key => $v_item)
			{
				?>
				<div class="checkbox">
					<label>
					<input type="checkbox" name="exclude_folder_<?php echo $l_key;?>" value="<?php echo $v_item['folder'];?>"<?php echo (1==$v_item['checked']?' checked':'');?>/>
						<?php echo $v_item['label'];?>
					</label>
				</div>
				<?php
			}
			?>
			</div>
		</div>
		<?php
	}
	?>
	<input type="submit" class="btn btn-primary btn-sm" value="Move" name="submit"/>
</form>
<script type="text/javascript">
$(function(){
	$('input[name="folder"]').off('change').on('change', function(e){
		if('all' == $(this).val())
		{
			$('#mm-exlude-folders').show();
		} else {
			$('#mm-exlude-folders').hide();
		}
	}).change();
});
</script>