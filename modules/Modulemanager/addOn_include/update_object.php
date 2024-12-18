<?php
error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
$s = array('_','.ver');
$r = array('.','');
if(isset($_GET['updateobject']))
{
	$s_title = $s_lib_version_path = $s_lib_path = $s_local_path = "";
	$s_source_version = "CurrentVersion";
	if(isset($_GET["sourceversion"])) $s_source_version = $_GET["sourceversion"];
	
	$v_r = array("/",".");
	if($_GET['updateobject'] == "fw")
	{
		$s_element = 'fw';
		$s_local_path = __DIR__."/../../../fw/";
		$s_title = $formText_UpdateFramework_input.': '.$s_element;
		
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
	if($_GET['updateobject'] == "account_elements")
	{
		$s_element = str_replace($v_r,"",$_GET[$_GET['updateobject']]);
		$s_local_path = __DIR__."/../../../".$s_element."/";
		$s_title = $formText_UpdateAccountElement_input.": ".$s_element;
		
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
	if($_GET['updateobject'] == "sys_module")
	{
		$s_module = str_replace($v_r,"",$_GET[$_GET['updateobject']]);
		$s_local_path = __DIR__."/../../".$s_module;
		$s_title = $formText_UpdateModule_input.": ".$s_module;
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
	
	if($s_lib_path == "" && $s_local_path == "")
	{
		echo $formText_WrongParameters_input;
		return;
	}
	
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
			$s_lib_path = $s_lib_version_path."/".$s_source_version."/".$s_element;
		}
	}
	
	if(is_dir($s_local_path))
	{
		$v_lib_version = $v_obj_version = $v_versions = array();
		$v_items = ftp_ext_get_filelist('',$s_lib_version_path);
		foreach($v_items as $v_item)
		{
			$v_versions[] = $v_item["name"];
		}
		rsort($v_versions);
		$v_items = ftp_ext_get_filelist('',$s_lib_path);
		foreach($v_items as $v_item)
		{
			if(substr($v_item["name"],-4) == ".ver")
			{
				$s_lib_version = str_replace($s,$r,substr($v_item["name"],0,-4));
			} else {
				if(isset($v_item['dir']))
				{
					$v_items2 = ftp_ext_get_filelist('',$s_lib_path."/".$v_item["name"]);
					foreach($v_items2 as $v_item2)
					{
						if(substr($v_item2["name"],-4) == ".ver")
						{
							$v_lib_version[$v_item["name"]] = str_replace($s,$r,substr($v_item2["name"],0,-4));
						}
					}
				}
			}
		}
		
		$v_items = scandir($s_local_path);
		foreach($v_items as $s_item)
		{
			if($s_item == "." || $s_item == "..") continue;
			if(substr($s_item,-4) == ".ver")
			{
				$s_obj_version = str_replace($s,$r,substr($s_item,0,-4));
			} else {
				if(is_dir($s_local_path."/".$s_item))
				{
					$v_items2 = scandir($s_local_path."/".$s_item);
					foreach($v_items2 as $s_item2)
					{
						if(substr($s_item2,-4) == ".ver")
						{
							$v_obj_version[$s_item] = str_replace($s,$r,substr($s_item2,0,-4));
						}
					}
				}
			}
		}
	}
}

$v_icons = array("" => "ok", "success" => "check", "warning" => "question-sign", "danger" => "alert");
$s_class = "";
if($s_obj_version < $s_lib_version) $s_class = "success";
if($s_obj_version > $s_lib_version) $s_class = "danger";
if(!is_numeric($s_obj_version)) $s_class = "danger";
?>
<div class="module-manager">
<h1><?php echo $s_title;?></h1>
<form method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&updateobject=".$_GET["updateobject"].(isset($_GET[$_GET["updateobject"]]) ? "&".$_GET["updateobject"]."=".$_GET[$_GET["updateobject"]] : "");?>">
<?php if(count($v_versions)>0) { ?>
<div><b><?php echo $formText_SelectLibraryVersion_input;?>:</b> <select name="sourceversion" id="mm_sourceversion"><?php
foreach($v_versions as $v_version)
{
	?><option value="<?php echo $v_version;?>"<?php echo ($s_source_version==$v_version ? ' selected="selected"':'');?>><?php echo $v_version;?></option><?php
}
?></select></div>
<?php } ?>
<?php
// Verify is library slave/main server synced
$b_server_is_synced = FALSE;
$data = array('data'=>json_encode(array("action"=>"is_server_synced", "server"=>$_SESSION['mm_library_host'])));
//call api
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$s_response = curl_exec($ch);
curl_close($ch);

$v_response = json_decode($s_response,true);
if(isset($v_response['status'], $v_response['synced']) && 1 == $v_response['status'] && 1 == $v_response['synced'])
{
	$b_server_is_synced = TRUE;
}
if(!$b_server_is_synced)
{
	?><div class="alert alert-warning"><?php echo $formText_LibraryServerIsNotCompletelySynced_Modulemanager;?></div><?php
}
?>
<table class="table">
<thead>
	<tr>
		<th colspan="2" width="60%"></th>
		<th><?php echo $formText_FromVersion_input;?></th>
		<th></th>
		<th><?php echo $formText_ToVersion_input;?></th>
		<th></th>
	</tr>
</thead>
<tbody>
	<tr class="<?php echo $s_class;?>">
		<td colspan="2"><?php echo $formText_Object_input;?></td>
		<td><?php echo $s_obj_version;?></td>
		<td><span class="glyphicon glyphicon-arrow-right"></span></td>
		<td><?php echo $s_lib_version;?></td>
		<td><span class="glyphicon glyphicon-<?php echo $v_icons[$s_class];?>"></span></td>
	</tr><?php
	$v_keys = array_merge($v_lib_version, $v_obj_version);
	foreach($v_keys as $s_key => $s_tmp)
	{
		$s_class = "";
		if($v_obj_version[$s_key] < $v_lib_version[$s_key]) $s_class = "success";
		if($v_obj_version[$s_key] > $v_lib_version[$s_key]) $s_class = "danger";
		if(!is_numeric($v_obj_version[$s_key])) $s_class = "warning";
		?><tr class="<?php echo $s_class;?>">
			<td width="7%" class="text-right"><span class="glyphicon glyphicon-triangle-right"></span></td>
			<td><?php echo $s_key;?></td>
			<td><?php echo $v_obj_version[$s_key];?></td>
			<td><span class="glyphicon glyphicon-arrow-right"></span></td>
			<td><?php echo $v_lib_version[$s_key];?></td>
			<td><span class="glyphicon glyphicon-<?php echo $v_icons[$s_class];?>"></span></td>
		</tr><?php
	}
	?>
</tbody>
</table>
<div>
	<?php if(count($v_versions)>0) { ?>
	<input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_Update_input;?>">
	<?php } ?>
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a>
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
});
</script>