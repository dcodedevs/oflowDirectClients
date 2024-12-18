<?php
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
$v_r = array("/",".");
if($_GET['lock_object'] == "fw")
{
	$s_element = 'fw';
	$s_local_path = __DIR__."/../../../fw/";
	$s_title = $formText_LockFramework_input;
	
	$s_category = "";
	if(is_file($s_local_path."/central.lib")) $s_category = trim(file_get_contents($s_local_path."/central.lib"));
	if($s_category == "") $s_category = 'frameworks';
	$v_central_lib_conf = explode('/', $s_category);
	if(count($v_central_lib_conf)>1)
	{
		if($v_central_lib_conf[1] != $s_element) $s_title .= ' ('.$v_central_lib_conf[1].')';
		$s_category = $v_central_lib_conf[0];
		$s_element = $v_central_lib_conf[1];
	}
	$s_lib_version_path = "/".$s_category."/".$s_element;
}
if($_GET['lock_object'] == "account_elements")
{
	$s_element = str_replace($v_r,"",$_GET[$_GET['lock_object']]);
	$s_category = 'account_elements';
	$s_local_path = __DIR__."/../../".$s_element;
	if(is_file($s_local_path."/central.lib")) $s_category = trim(file_get_contents($s_local_path."/central.lib"));
	if($s_category == "") $s_category = "account_elements";
	$v_central_lib_conf = explode('/', $s_category);
	if(count($v_central_lib_conf)>1)
	{
		$s_category = $v_central_lib_conf[0];
		$s_element = $v_central_lib_conf[1];
	}
	$s_title = $formText_LockAccountElement_input.": ".$s_element;
	$s_lib_version_path = "/".$s_category."/".$s_element;
}
if($_GET['lock_object'] == "sys_module")
{
	$s_element = str_replace($v_r,"",$_GET[$_GET['lock_object']]);
	$s_category = 'modules';
	$s_local_path = __DIR__."/../../".$s_element;
	if(is_file($s_local_path."/central.lib")) $s_category = trim(file_get_contents($s_local_path."/central.lib"));
	if($s_category == "") $s_category = "modules";
	$v_central_lib_conf = explode('/', $s_category);
	if(count($v_central_lib_conf)>1)
	{
		$s_category = $v_central_lib_conf[0];
		$s_element = $v_central_lib_conf[1];
	}
	$s_title = $formText_LockModule_input.": ".$s_element;
	$s_lib_version_path = "/".$s_category."/".$s_element;
}

$s_obj_version = get_folder_version($s_local_path);
//get recommended version
$data = array('data'=>json_encode(array('action'=>'get_recommended_update_version', 'object'=>$s_lib_version_path, 'version'=>$s_obj_version)));
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
	$s_lib_version = str_replace('_', '.', $v_data['version_number']);
}
?>
<div class="module-manager">
<h1><?php echo $s_title;?></h1>
<?php
if($s_obj_version != $s_lib_version)
{
	?><div class="alert alert-warning"><strong><?php echo $formText_Warning_input;?>!</strong> <?php echo $formText_ObjectIsNotUpToDate_input.' ('.$formText_Local_input.': '.$s_obj_version.', '.$formText_Library_input.': '.$s_lib_version.')';?></div><?php
}
?>
<form method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&lock_object=".$_GET["lock_object"]."&".$_GET["lock_object"]."=".$_GET[$_GET["lock_object"]];?>">
<div>
	<input type="hidden" name="category" value="<?php echo $s_category;?>">
	<input type="hidden" name="element" value="<?php echo $s_element;?>">
	<textarea name="comment" rows="5" style="width:100%;"><?php echo $formText_ObjecLockedInAccount_Modulemanager.' '.$_GET['accountname'];?></textarea>
</div>
<div>
	<input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_Lock_input;?>">
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a>
</div>
</form>
</div>