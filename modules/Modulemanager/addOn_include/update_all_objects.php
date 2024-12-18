<?php
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");
if(!function_exists("get_folder_version")) include(__DIR__."/fn_get_folder_version.php");
if(!function_exists("get_folder_structure_checksum")) include(__DIR__."/fn_get_folder_structure_checksum.php");
$s = array('_','.ver');
$r = array('.','');

$v_icons = array("" => "ok", "success" => "check", "warning" => "question-sign", "danger" => "alert");

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
?>
<div class="module-manager">
<h1><?php echo $formText_UpdateAccountObjectsFromLibrary_input;?></h1>
<table class="table">
<thead>
	<tr>
		<th></th>
		<th width="40%"></th>
		<th><?php echo $formText_Locked_input;?></th>
		<th><?php echo $formText_Checksum_input;?></th>
		<th><?php echo $formText_FromVersion_input;?></th>
		<th></th>
		<th><?php echo $formText_ToVersion_input;?></th>
		<th></th>
	</tr>
</thead>
<tbody>
	<?php
	/*$v_objects = array(
		"fw" => array($formText_Framework_input, "/frameworks/fw", "/frameworks/fw/CurrentVersion/fw", __DIR__."/../../../fw/", '&updateobject=fw'),
		"api" => array($formText_Api_input, "/account_elements/api", "/account_elements/api/CurrentVersion/api", __DIR__."/../../../api/", '&updateobject=account_elements&account_elements=api'),
		"ckeditor" => array($formText_CkEditor_input, "/account_elements/ckeditor", "/account_elements/ckeditor/CurrentVersion/ckeditor", __DIR__."/../../../ckeditor/", '&updateobject=account_elements&account_elements=ckeditor'),
		"kcfinder" => array($formText_KcFinder_input, "/account_elements/kcfinder", "/account_elements/kcfinder/CurrentVersion/kcfinder", __DIR__."/../../../kcfinder/", '&updateobject=account_elements&account_elements=kcfinder'),
		"min" => array($formText_Minifier_input, "/account_elements/min", "/account_elements/min/CurrentVersion/min", __DIR__."/../../../min/")
	);*/
	$s_source_version = 'CurrentVersion';
	$v_items = array(
		"fw" => array($formText_Framework_input, "frameworks"),
		"api" => array($formText_Api_input, "account_elements"),
		"ckeditor" => array($formText_CkEditor_input, "account_elements"),
		"kcfinder" => array($formText_KcFinder_input, "account_elements"),
		"min" => array($formText_Minifier_input, "account_elements")
	);
	$v_objects = array();
	foreach($v_items as $s_element => $v_item)
	{
		$s_local_path = __DIR__."/../../../".$s_element;
		$s_lib_element = $s_element;
		$s_lib_category = $v_item[1];
		if(is_file($s_local_path."/central.lib"))
		{
			$s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
			if($s_lib_category == "") $s_lib_category = $v_item[1];
			$v_central_lib_conf = explode('/', $s_lib_category);
			if(count($v_central_lib_conf)>1)
			{
				$s_lib_category = $v_central_lib_conf[0];
				$s_lib_element = $v_central_lib_conf[1];
			}
			
		}
		$s_lib_version_path = "/".$s_lib_category."/".$s_lib_element;
		$s_lib_path = "/".$s_lib_category."/".$s_lib_element."/".$s_source_version."/".$s_lib_element;
		
		$v_objects[$s_element] = array(
			$v_item[0],
			$s_lib_version_path,
			$s_lib_path,
			$s_local_path,
			'&updateobject='.$s_lib_category.'&'.$s_lib_category.'='.$s_element
		);
	}
	
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
					$s_local_path = __DIR__."/../../".$s_module;
					if(is_file($s_local_path."/central.lib"))
					{
						$s_lib_module = $s_module;
						$s_lib_category = trim(file_get_contents($s_local_path."/central.lib"));
						if($s_lib_category == "") $s_lib_category = "modules";
						$v_central_lib_conf = explode('/', $s_lib_category);
						if(count($v_central_lib_conf)>1)
						{
							$s_lib_category = $v_central_lib_conf[0];
							$s_lib_module = $v_central_lib_conf[1];
						}
						$s_lib_version_path = "/".$s_lib_category."/".$s_lib_module;
						$s_lib_path = "/".$s_lib_category."/".$s_lib_module."/".$s_source_version."/".$s_lib_module;
						
						$v_objects['modules/'.$s_module] = array($s_module, $s_lib_version_path, $s_lib_path, $s_local_path, '&updateobject=sys_module&sys_module='.$s_module);
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
		list($s_title, $s_lib_version_path, $s_lib_path, $s_local_path, $s_url) = $v_item;
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
			$s_lib_path = $s_lib_version_path."/".$s_source_version."/".$s_element;
			
			$v_items = ftp_ext_get_filelist('',$s_lib_path);
			foreach($v_items as $v_item)
			{
				if(substr($v_item["name"],-4) == ".ver")
				{
					$s_lib_version = str_replace($s,$r,substr($v_item["name"],0,-4));
				}
			}
		}
		$s_obj_version = get_folder_version($s_local_path);
		
		//verify copied object checksum
		$s_checksum_class = 'danger';
		$s_local_folder_checksum = get_folder_structure_checksum($s_local_path, TRUE);
		$v_param = array
		(
			'action'=>'verify_object_checksum',
			'path'=>$s_lib_version_path."/".str_replace('.', '_', $s_obj_version)."/".$s_element,
			'checksum'=>$s_local_folder_checksum/*,
			'excludes'=>array()*/
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
		}
		
		$s_class = "";
		if($s_obj_version < $s_lib_version) $s_class = "success";
		if($s_obj_version > $s_lib_version) $s_class = "danger";
		if(!is_numeric($s_obj_version)) $s_class = "danger";
		?>
		<tr class="mm-update-object <?php echo $s_class;?>">
			<td><input type="checkbox"<?php echo ('success'==$s_class&&''==$s_checksum_class?" checked":"");?> data-id="<?php echo $l_counter;?>" data-url="<?php echo $s_url;?>" data-version="<?php echo $s_source_version;?>"></td>
			<td><?php echo $s_title;?></td>
			<td>
				<?php
				$s_key = str_replace('/', '_', $s_object);
				if(isset($v_locked_elements[$s_key])) { ?>
				<span class="locked-object glyphicon glyphicon-info-sign" data-date="<?php echo $v_locked_elements[$s_key]['lock_date'];?>" data-username="<?php echo $v_locked_elements[$s_key]['lock_username'];?>" data-comment="<?php echo $v_locked_elements[$s_key]['lock_comment'];?>" data-url="<?php echo $extradir.'/addOn_include/addlibrary.php?pageID='.$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule.'&unlock_object='.$s_lib_category.':'.$s_element.'&_='.rand(10000,99999);?>"></span>
				<?php } else { echo '-'; } ?>
			</td>
			<td><span class="glyphicon glyphicon-<?php echo $v_icons[$s_checksum_class];?>"></span></td>
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
	$('span.locked-object').on('click', function(e){
		e.preventDefault();
		fw_info_message_add('info', '<b>Locked on:</b> ' + $(this).data('date') + '&nbsp;&nbsp;&nbsp;<b>Locked by:</b> ' + $(this).data('username') + '<br/><b>Comment:</b> ' + $(this).data('comment') + '<?php /*?><br/><b><a style="color:black;" href="' + $(this).data('url') + '"><?php echo $formText_Unlock_input;?></a></b><?php */?>', true);
	});
});
function update_all_objects(objects)
{
	if(objects.length > 0)
	{
		var id = objects.pop();
		var $obj = $('.mm-update-object input[data-id='+id+']');
		
		$.ajax({
			url: '<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>' + $obj.data('url'),
			cache: false,
			type: "POST",
			dataType: "json",
			data: { update_all_request: 1, sourceversion: $obj.data('version') },
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
<style type="text/css">
span.locked-object { cursor:pointer; }
</style>