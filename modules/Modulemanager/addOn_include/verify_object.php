<?php
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
$s = array('_','.ver');
$r = array('.','');
$v_error_msg = array();

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

if(isset($_GET['verifyobject']))
{
	$s_title = $s_lib_version_path = $s_lib_path = $s_local_path = "";
	$s_source_version = "CurrentVersion";
	if(isset($_GET["sourceversion"])) $s_source_version = $_GET["sourceversion"];

	$v_r = array("/",".");
	if($_GET['verifyobject'] == "fw")
	{
		$s_element = 'fw';
		$s_local_path = __DIR__."/../../../fw/";
		$s_title = $formText_VerifyFrameworkAgainstLibrary_input.': '.$s_element;

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
		$s_lib_path = "/".$s_lib_category."/".$s_element."/".$s_source_version."/".$s_element;
	}
	if($_GET['verifyobject'] == "account_elements")
	{
		$s_element = str_replace($v_r,"",$_GET[$_GET['verifyobject']]);
		$s_local_path = __DIR__."/../../../".$s_element."/";
		$s_title = $formText_VerifyObjectAgainstLibrary_input.": ".$s_element;

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
		$s_lib_path = "/".$s_lib_category."/".$s_element."/".$s_source_version."/".$s_element;
	}
	if($_GET['verifyobject'] == "sys_module")
	{
		$s_module = str_replace($v_r,"",$_GET[$_GET['verifyobject']]);
		$s_local_path = __DIR__."/../../".$s_module;
		$s_title = $formText_VerifyModuleAgainstLibrary_input.": ".$s_module;

		$s_lib_category = "";
		if(is_file($s_local_path."/central.lib")) $s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
		if($s_lib_category == "") $s_lib_category = "modules";
		$v_central_lib_conf = explode('/', $s_lib_category);
		if(count($v_central_lib_conf)>1)
		{
			if($v_central_lib_conf[1] != $s_module) $s_title .= ' ('.$v_central_lib_conf[1].')';
			$s_lib_category = $v_central_lib_conf[0];
			$s_module = $v_central_lib_conf[1];
		}
		$s_lib_version_path = "/".$s_lib_category."/".$s_module;
		$s_lib_path = "/".$s_lib_category."/".$s_module."/".$s_source_version."/".$s_module;
	}
	if($_GET['verifyobject'] == "customized_elements")
	{
		$s_element = str_replace($v_r,"",$_GET[$_GET['verifyobject']]);
		$s_local_path = BASEPATH."elementsCustomized/".$s_element."/";
		$s_title = $formText_VerifyObjectAgainstLibrary_input.": ".$s_element;

		$s_lib_category = "";
		if(is_file($s_local_path."/central.lib")) $s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
		if($s_lib_category == "") $s_lib_category = 'customized_elements';
		$v_central_lib_conf = explode('/', $s_lib_category);
		if(count($v_central_lib_conf)>1)
		{
			if($v_central_lib_conf[1] != $s_element) $s_title .= ' ('.$v_central_lib_conf[1].')';
			$s_lib_category = $v_central_lib_conf[0];
			$s_element = $v_central_lib_conf[1];
		}
		$s_lib_version_path = "/".$s_lib_category."/".$s_element;
		$s_lib_path = "/".$s_lib_category."/".$s_element."/".$s_source_version."/".$s_element;
	}

	if($s_lib_path == "" && $s_local_path == "")
	{
		$v_error_msg[] = $formText_WrongParameters_input;
	} else {
		$s_lib_version_number = '';
		if(!isset($_GET["sourceversion"]))
		{
			//get recommended version
			$data = array('data'=>json_encode(array('action'=>'get_recommended_update_version', 'object'=>$s_lib_version_path, 'version'=>get_folder_version($s_local_path))));
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
				$v_path = explode("/", $s_lib_version_path);
				$s_element = array_pop($v_path);
				$s_source_version = $v_data['version'];
				$s_lib_version_number = $v_data['version_number'];
				$s_lib_path = $s_lib_version_path."/".$s_source_version."/".$s_element;
			}
		}

		$v_local_object = array();
		$v_local_object = scanAllDirectory($s_local_path, $s_local_path, $v_local_object);
		$v_local_object = sortFiles($v_local_object, $s_local_path);

		/*$data = array('data'=>json_encode(array("action"=>"verify_object","object_path"=>$s_lib_path,"structure"=>$v_local_object)));
		//call api
		$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);

		$v_data = json_decode($response,true);
		if(isset($v_data['status']) && $v_data['status'] == 1)*/
		$v_param = array(
			'auth_token' => $v_server['api_token'],
			'action' => 'verify_object',
			'object_path' => $s_lib_path,
			'structure' => $v_local_object,
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
			$v_difference = $v_response['difference'];
			$v_versions = array();
			$v_items = ftp_ext_get_filelist('',$s_lib_version_path);
			foreach($v_items as $v_item)
			{
				$v_versions[] = $v_item["name"];
			}
			rsort($v_versions);
		} else {
			$v_error_msg[] = $formText_ErrorOccuredCommunicatingWithLibrary_Output.': '.$v_response['message'];
		}
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

$v_users_allowed_to_create_version = array();
$data = array('data'=>json_encode(array("action"=>"get_user_list")));
//call api
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$s_response = curl_exec($ch);
curl_close($ch);

$v_response = json_decode($s_response,true);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['items'] as $v_item)
	{
		if(1 == $v_item['allow_add_version_from_modulemanager']) $v_users_allowed_to_create_version[] = $v_item['username'];
	}
}


$s_local_path_relative = realpath($s_local_path);
$v_split = explode('/'.$accountname.'/', $s_local_path_relative);
$s_local_path_relative = $v_split[1];

$v_icons = array("" => "ok", "success" => "check", "warning" => "question-sign", "danger" => "alert");
$s_class = "";
if($s_obj_version < $s_lib_version) $s_class = "success";
if($s_obj_version > $s_lib_version) $s_class = "danger";
if(!is_numeric($s_obj_version)) $s_class = "danger";
?>
<div class="module-manager">
<h1><?php echo $s_title;?></h1>
<form method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&updateobject=".$_GET["verifyobject"].(isset($_GET[$_GET["verifyobject"]]) ? "&".$_GET["verifyobject"]."=".$_GET[$_GET["verifyobject"]] : "");?>">
<?php if(count($v_versions)>0) { ?>
<div><b><?php echo $formText_SelectLibraryVersion_input;?>:</b> <select name="sourceversion" id="mm_sourceversion"><?php
foreach($v_versions as $v_version)
{
	?><option value="<?php echo $v_version;?>"<?php echo ($s_source_version==$v_version ? ' selected="selected"':'');?>><?php echo $v_version;?></option><?php
}
?></select></div>
<?php } ?>
<?php
if(!$b_server_is_synced)
{
	?><div class="alert alert-warning"><?php echo $formText_LibraryServerIsNotCompletelySynced_Modulemanager;?></div><?php
}
?>
<?php if(sizeof($v_error_msg)==0) { ?>
<table class="table">
<thead>
	<tr>
		<th><?php echo $formText_Object_input;?></th>
		<th colspan="3"><?php echo $formText_Status_input;?></th>
	</tr>
</thead>
<tbody><?php
	if(count($v_difference)>0)
	{
		$v_status_icon = array("A" => "plus", "C" => "pencil", "D" => "minus");
		$v_status_class = array("A" => "info", "C" => "warning", "D" => "danger");
		$v_status = array("A" => $formText_Added_input, "C" => $formText_Changed_input, "D" => $formText_Deleted_input);
		foreach($v_difference as $s_object => $s_status)
		{
			?><tr class="<?php echo $v_status_class[$s_status];?>">
				<td><?php echo $s_object;?></td>
				<td width="5%"><span class="glyphicon glyphicon-<?php echo $v_status_icon[$s_status];?>"></span></td>
				<td width="15%"><?php echo $v_status[$s_status];?></td>
				<td width="5%"><?php if($s_status=='C') { ?><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=compare_files&comparefiles=".base64_encode($s_lib_path.$s_object.'[:]'.$s_local_path_relative.$s_object)."&returl=".base64_encode($_SERVER['PHP_SELF']."?".get_curent_GET_params());?>" class="optimize"><span class="glyphicon glyphicon-eye-open"></span></a><?php } ?></td>
			</tr><?php
		}
	} else {
		?><tr><td colspan="3"><?php echo $formText_NoDifferenceHasBeenFound_input;?></td></tr><?php
	}
	?>
</tbody>
</table>
<?php } else {
	foreach($v_error_msg as $s_msg)
	{
		?><div class="alert alert-danger"><?php echo $s_msg;?></div><?php
	}
}
$s_mm_add_object_instance = uniqid();
$_SESSION['mm_add_object_instance'] = $s_mm_add_object_instance;
?>
<div>
	<div class="row">
	<div class="col-xs-6">
	<?php if(count($v_versions)>0) { ?>
	<input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_Update_input;?>" style="margin-right:10px;">
	<?php } ?>
	<?php
	$s_key = $s_lib_category.'_'.$s_element;
	if(isset($v_locked_elements[$s_key])) { ?>
	<span class="locked-object glyphicon glyphicon-info-sign" data-date="<?php echo $v_locked_elements[$s_key]['lock_date'];?>" data-username="<?php echo $v_locked_elements[$s_key]['lock_username'];?>" data-comment="<?php echo $v_locked_elements[$s_key]['lock_comment'];?>" data-url="<?php echo $extradir.'/addOn_include/addlibrary.php?pageID='.$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule.'&unlock_object='.$s_lib_category.':'.$s_element.'&_='.rand(10000,99999);?>"></span>
	<?php } else { ?>
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=lock_object&lock_object=".$_GET["verifyobject"].(isset($_GET[$_GET["verifyobject"]]) ? "&".$_GET["verifyobject"]."=".$_GET[$_GET["verifyobject"]] : "")."&_=".rand(10000,99999);?>" class="btn btn-warning optimize" style="margin-right:10px;"><?php echo $formText_Lock_input;?></a>
	<?php } ?>
	</div>
	<div class="col-xs-4">
	<?php if(in_array($_COOKIE['username'], $v_users_allowed_to_create_version)) { ?>
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input&includefile=add_object&addobject=".$_GET["verifyobject"].(isset($_GET[$_GET["verifyobject"]]) ? "&".$_GET["verifyobject"]."=".$_GET[$_GET["verifyobject"]] : "")."&instance=".urlencode($s_mm_add_object_instance)."&_=".rand(10000,99999);?>" class="btn btn-info" style="margin-right:10px;" onClick="return confirm('<?php echo $formText_AreYouSureYouWantToCreateNewVersion_Modulemanager;?>?');"><?php echo $formText_CreateVersion_input;?></a>
	<?php } ?>
	</div>
	<div class="col-xs-2">
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize" style="margin-right:10px;"><?php echo $formText_GoBack_input;?></a>
	</div>
	</div>
</div>
</form>
</div>
<script type="text/javascript">
$(function(){
	$('#mm_sourceversion').on('change',function(){
		var $obj = $("<a>").addClass("optimize").attr("href","<?php echo $_SERVER['PHP_SELF']."?".get_curent_GET_params(array('sourceversion'));?>&sourceversion=" + $(this).val());
		$(".module-manager").append($obj);
		$obj.trigger("click");
	});
	$('span.locked-object').on('click', function(e){
		e.preventDefault();
		fw_info_message_add('info', '<b>Locked on:</b> ' + $(this).data('date') + '&nbsp;&nbsp;&nbsp;<b>Locked by:</b> ' + $(this).data('username') + '<br/><b>Comment:</b> ' + $(this).data('comment') + '<br/><b><a style="color:black;" href="' + $(this).data('url') + '"><?php echo $formText_Unlock_input;?></a></b>', true);
	});
});
</script>
<?php
//get all files from directory and subdirectories
function scanAllDirectory($s_path, $s_path_remove, $v_files)
{
	$v_exclude = array('..', '.');
	$v_items = array_diff(scandir($s_path), $v_exclude);
	foreach($v_items as $s_file){
		if(is_dir($s_path."/".$s_file)){
			$v_files = scanAllDirectory($s_path."/".$s_file, $s_path_remove, $v_files);
		}else{
			$s_path_real = str_replace($s_path_remove, "", $s_path);
			array_push($v_files, array($s_path_real => array("filename"=>$s_file, "hash"=>sha1_file($s_path."/".$s_file), "path"=>$s_path_real)));
		}
	}
	return $v_files;
}

//sort files array, grouping by folder and changing array keys
function sortFiles($v_files, $s_root_path){
	$v_return = array();
	foreach($v_files as $s_key => $v_file){
		foreach($v_file as $s_path => $v_file_info){
			$s_path = str_replace($s_root_path, "", $s_path);
			if(isset($v_return[$s_path])){
				$v_item = $v_return[$s_path];
			}else{
				$v_item = array();
			}
			array_push($v_item, $v_file_info);
			$v_return[$s_path] = $v_item;
		}
	}
	return $v_return;
}
