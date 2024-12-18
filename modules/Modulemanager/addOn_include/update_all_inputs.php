<?php
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
if(!function_exists("get_folder_structure_checksum")) include(__DIR__."/fn_get_folder_structure_checksum.php");
$s = array('_','.ver');
$r = array('.','');

$v_icons = array("" => "ok", "success" => "check", "warning" => "question-sign", "danger" => "alert");

$sourcemodulepath = 'defaultmodule/CurrentVersion/defaultmodule';
$modulebasedir = "modules/".$sourcemodulepath;
$modulebasedirfiles = ftp_ext_get_filelist('',"modules/".$sourcemodulepath);
$s_update = $modulebasedir.'/input:input:0::'.$modulebasedir.'/input/includes:input/includes:1::'.$modulebasedir.'/input/settings:input/settings:0';
foreach($modulebasedirfiles as $name)
{
	if($name['name'] == 'input')
	{
		$d1 = ftp_ext_get_filelist('',$modulebasedir."/".$name['name']);
		foreach($d1 as $entry1)
		{
			if($entry1['name'] == 'buttontypes' || $entry1['name'] == 'fieldtypes')
			{  
				$childs = array();
				$d2 = ftp_ext_get_filelist('',$modulebasedir."/".$name['name']."/".$entry1['name']);
				foreach($d2 as $entry2)
				{
					if(array_key_exists("dir",$entry2))
					{
						$over = "";
						$d3 = ftp_ext_get_filelist('',$modulebasedir."/".$name['name']."/".$entry1['name'].'/'.$entry2['name']);
						foreach($d3 as $entry3)
						{
							if(strpos($entry3['name'],'.ver') !== false)
							{
								$ver = str_replace($s,$r,$entry3['name']);
								break;
							}
						}
						//$v_update['update_folder_'.$entry1['name'].$entry2['name']] = $modulebasedir."/".$name['name']."/".$entry1['name'].'/'.$entry2['name'].":input/".$entry1['name'].'/'.$entry2['name'].':1';
						$s_update .= '::'.$modulebasedir."/".$name['name']."/".$entry1['name'].'/'.$entry2['name'].":input/".$entry1['name'].'/'.$entry2['name'].':1';
					}
				}
			}
			else if(strpos($entry1['name'],'.ver') !== false)
			{
				//$v_update['update_folder_standard'] = $modulebasedir.'/input:input:0::'.$modulebasedir.'/input/includes:input/includes:1::'.$modulebasedir.'/input/settings:input/settings:0';
			}
		}
		continue;
	}
}
//$s_update = htmlentities(json_encode($v_update));
?>
<div class="module-manager">
<h1><?php echo $formText_UpdateAllModuleInputs_input;?></h1>
<table class="table">
<thead>
	<tr>
		<th></th>
		<th width="40%"></th>
		<th><?php echo $formText_FromVersion_input;?></th>
		<th></th>
		<th><?php echo $formText_ToVersion_input;?></th>
		<th></th>
	</tr>
</thead>
<tbody>
	<?php
	$s_source_version = 'CurrentVersion';
	
	$s_module_path = __DIR__."/../../../modules/";
	if(is_dir($s_module_path))
	{
		$o_scan = scandir($s_module_path);
		natcasesort($o_scan);
		foreach($o_scan as $s_module)
		{
			if($s_module != '..' && $s_module != '.')
			{
				$o_query = $o_main->db->query('select * from moduledata where name = ?', array($s_module));
				if($o_query && $o_query->num_rows()>0)
				{
					$v_module = $o_query->row_array();
					$s_local_path = __DIR__."/../../".$s_module;
					if(is_dir($s_local_path."/input"))
					{
						$s_lib_module = 'defaultmodule';//$s_module;
						$s_lib_category = "modules";
						$s_lib_version_path = "/".$s_lib_category."/".$s_lib_module;
						$s_lib_path = "/".$s_lib_category."/".$s_lib_module."/".$s_source_version."/".$s_lib_module;
						
						$v_objects['modules/'.$s_module] = array($s_module, $s_lib_version_path, $s_lib_path, $s_local_path, '&updat=1'.$s_module, $v_module['type']);
					}
				}
			}
		}
	}
	
	$l_counter = 1;
	foreach($v_objects as $s_object => $v_item)
	{
		if(!is_dir(__DIR__."/../../../".$s_object."/")) continue;
		
		$s_lib_version = '';
		list($s_module, $s_lib_version_path, $s_lib_path, $s_local_path, $s_url, $l_type) = $v_item;
		$v_path = explode("/", $s_lib_version_path);
		$s_element = array_pop($v_path);
		
		//get recommended version
		$v_param = array('data'=>json_encode(array('action'=>'get_recommended_update_version', 'object'=>$s_lib_version_path, 'version'=>get_folder_version($s_local_path))));
		$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_param));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$s_response = curl_exec($ch);
		curl_close($ch);
		
		$v_recommended_version = json_decode($s_response, TRUE);
		if(isset($v_recommended_version['status']) && $v_recommended_version['status'] == 1)
		{
			$s_source_version = $v_recommended_version['version'];
			$s_lib_path = $s_lib_version_path."/".$s_source_version."/".$s_element."/input";
			
			$v_items = ftp_ext_get_filelist('',$s_lib_path);
			foreach($v_items as $v_item)
			{
				if(substr($v_item["name"],-4) == ".ver")
				{
					$s_lib_version = str_replace($s,$r,substr($v_item["name"],0,-4));
				}
			}
		}
		$s_obj_version = get_folder_version($s_local_path."/input");
		
		//verify copied object checksum
		/*$s_checksum_class = 'danger';
		$s_local_folder_checksum = get_folder_structure_checksum($s_local_path, TRUE);
		$v_param = array
		(
			'action'=>'verify_object_checksum',
			'path'=>$s_lib_version_path."/".str_replace('.', '_', $s_obj_version)."/".$s_element,
			'checksum'=>$s_local_folder_checksum//,
			//'excludes'=>array()
		);
		$v_param = array('data'=>json_encode($v_param));
		$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_param));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$s_response = curl_exec($ch);
		curl_close($ch);
		
		$v_checksum = json_decode($s_response, TRUE);
		if(isset($v_checksum['status']) && $v_checksum['status'] == 1)
		{
			$s_checksum_class = '';
		}*/
		
		$s_class = "";
		if($s_obj_version < $s_lib_version) $s_class = "success";
		if($s_obj_version > $s_lib_version) $s_class = "danger";
		if(!is_numeric($s_obj_version)) $s_class = "danger";
		if($l_type == 10) $s_class = '';
		?>
		<tr class="mm-update-object <?php echo $s_class;?>">
			<td><input type="checkbox"<?php echo ($s_class=="success"?" checked":"");?> data-id="<?php echo $l_counter;?>" data-module="<?php echo $s_module;?>" data-version="<?php echo $s_source_version;?>" data-source="<?php echo substr($s_lib_version_path, 9)."/".$s_source_version."/".$s_element;?>" value="<?php echo $s_update;?>">
			</td>
			<td><?php echo $s_module;?></td>
			<td><?php echo $s_obj_version;?></td>
			<td><span class="glyphicon glyphicon-arrow-right"></span></td>
			<td><?php echo $s_lib_version;?></td>
			<td><span class="glyphicon glyphicon-<?php echo $v_icons[$s_class];?>"></span></td>
		</tr>
		<?php
		$l_counter++;
	}
	?>
</tbody>
</table>
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
<div>
	<input id="mm-update-objects-btn" type="button" class="btn btn-success" name="submbtn" value="<?php echo $formText_Update_input;?>">
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a>
</div>
</div>
<script type="text/javascript">
$(function(){
	$('#mm-update-objects-btn').on('click',function(e){
		e.preventDefault();
		fw_loading_start();
		var objects = new Array();
		$('.mm-update-object input[type=checkbox]').each(function(){
			if($(this).is(':checked'))
			{
				objects.push($(this).data('id'));
			}
		});
		update_all_objects(objects);
	});
});
function update_all_objects(objects)
{
	if(objects.length > 0)
	{
		var id = objects.pop();
		var $obj = $('.mm-update-object input[data-id='+id+']');
		var post = { updatemodule: $obj.data('module'), sourcemodule_original: $obj.data('source'), sourcemodule: $obj.data('source'), sourceversion: $obj.data('version'), update_all: 1, update_folder_all: $obj.val() };
		/*$.each(JSON.parse($obj.val()), function(key, value){
			post[key] = value;
		});*/
		$.ajax({
			url: '<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>&update=1',
			cache: false,
			type: "POST",
			dataType: "json",
			data: post,
			success: function(json){
				update_all_objects(objects);
			}
		}).fail(function(){
			fw_info_message_add('error', '<?php echo $formText_ErrorOccurredHandlingRequest_input;?>', true, true);
			fw_loading_end();
		});
	} else {
		fw_loading_end();
		window.location = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>';
	}
}
</script>