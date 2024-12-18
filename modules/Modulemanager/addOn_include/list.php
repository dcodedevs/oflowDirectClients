<?php
if($variables->developeraccess >= 20)
{
	//Fix UniqueID of moduledata
	if(!$o_main->db->field_exists('uniqueID', 'moduledata'))
	{
		$o_main->db->simple_query("ALTER TABLE moduledata ADD uniqueID INT(11) NOT NULL AFTER `id`;");
		$o_main->db->simple_query("ALTER TABLE moduledata CHANGE id id INT(11) NOT NULL");
		$o_main->db->simple_query("update moduledata set uniqueID = id");
		$o_main->db->simple_query("ALTER TABLE moduledata DROP PRIMARY KEY, ADD PRIMARY KEY(`uniqueID`)");
		$o_main->db->simple_query("ALTER TABLE moduledata CHANGE uniqueID uniqueID INT( 11 ) NOT NULL AUTO_INCREMENT");
	}
	if(!$o_main->db->field_exists('deactivated', 'moduledata'))
	{
		$o_main->db->simple_query("ALTER TABLE moduledata ADD deactivated INT NOT NULL");
	}
	
	$l_random = rand(1000,999999);
	$v_module_types = $v_module_names = array();
	$o_query = $o_main->db->query('select * from moduledata');
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $row)
	{
		$v_module_types[$row['name']] = $row['type'];
		$v_module_names[] = $row['name'];
	}
	$dir = $_SERVER['DOCUMENT_ROOT']."/accounts/$accountname/modules/";
	
	// Open a known directory, and proceed to read its contents
	?>
	<div class="module-manager">
	<?php
	include(__DIR__."/../../../ftpConnect.php");
	include(__DIR__."/fn_get_folder_version.php");
		
	if(!$ftplogin)
	{
		print_info($formText_FtpUserIsNotCreatedPleaseWaitAndRefreshPageAfterMinute_input, $extradir.'/addOn_include/elementsInput/warning.jpg');
		$passfile = file_get_contents("../ftpConnect.php");
		$pass = substr($passfile,strrpos($passfile,"FTP\",\"")+6); 
		$pass = substr($pass,0,strrpos($pass,"\""));
		
		$homefolder = realpath(__DIR__.'/../../../');
		//echo "homefolder = $homefolder";
		$v_accountinfo = array();
		$o_query = $o_main->db->query('SELECT * FROM accountinfo');
		if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();
		if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
		$response = APIconnectAccount("accountcreateftpuser", $v_accountinfo['accountname'], $v_accountinfo['password'], array('ACC_NAME'=>$v_accountinfo['accountname'],'USERNAME_FTP'=>$v_accountinfo['accountname']."FTP",'CRYPT_PASSWORD'=>crypt($pass,time()),"HOMEFOLDER"=>$homefolder));
		//echo "response = $response";
	}
	
	if(isset($_GET['restore']))
	{
		print_info($formText_FilePermissionsWillBeResetedAfterMinute_input, $extradir.'/addOn_include/elementsInput/warning.jpg');
		unset($_GET['restore']);
	}
	if(isset($_GET['updenc']))
	{
		print_info($formText_FileEncodingWillBeChangedAfterMinute_input, $extradir.'/addOn_include/elementsInput/warning.jpg');
		unset($_GET['updenc']);
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
	
	$linkStandard = "pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID'] : '').(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield'] : '');
	?>
	<style>
	.word-break { word-break:break-all; }
	</style>
	<table class="table table-striped table-hover table-condensed max-col">
	<thead>
		<tr>
			<th><?php echo $formText_Id_input;?></th>
			<th class="word-break"><?php echo $formText_ModuleName_input;?></th>
			<th><?php echo $formText_Versions_input;?></th>
			<th><?php echo $formText_Custom_input;?></th>
			<th><?php echo $formText_Local_input;?></th>
			<th class="word-break"><?php echo $formText_LocalName_input;?></th>
			<th class="word-break"><?php echo $formText_Edition_input;?></th>
			<th><?php echo $formText_Mode_input;?></th>
			<th><?php echo $formText_Type_input;?></th>
			<th><?php echo $formText_Action_input;?></th>
			<th><?php echo $formText_Lock_input;?></th>
			<th></th>
		</tr>
	</thead>
	<tbody><?php
	$v_grouped_modules = array();
	if(is_dir($dir))
	{
		$o_scan = scandir($dir);
		natcasesort($o_scan);
		foreach($o_scan as $s_module)
		{
			if($s_module != '..' && $s_module != '.')
			{
				$v_moduledata = array();
				$o_query = $o_main->db->query('select * from moduledata where name = ?', array($s_module));
				if($o_query && $o_query->num_rows()>0)
				{
					$v_moduledata = $o_query->row_array();
				}
				$l_group = 0;
				if($v_moduledata['type'] == 10) $l_group = 10;
				$v_grouped_modules[$l_group][] = $s_module;
				
				$o_query = $o_main->db->query('select * from moduledata where virtual_module_source = ?', array($s_module));
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $v_row)
				{
					$l_group = 0;
					if($v_row['type'] == 10) $l_group = 10;
					$v_grouped_modules[$l_group][] = $v_row['name'];
				}
			}
		}
	}
	$v_groups = array_keys($v_grouped_modules);
	sort($v_groups);
	$s_module_groups = array(0 => $formText_NormalModules_input, 10 => $formText_SystemModules_input);
	foreach($v_groups as $l_group)
	{
		$v_modules = $v_grouped_modules[$l_group];
		?><tr><td colspan="10" style="padding-top:10px; text-align:center; font-weight:bold;"><?php echo $s_module_groups[$l_group];?></td></tr><?php
		foreach($v_modules as $s_module)
		{
			$inputversion = $moduleversion = '';
			if($s_module != '..' && $s_module != '.')
			{
				$b_is_library_object = FALSE;
				if(is_file(__DIR__.'/../../'.$s_module.'/central.lib'))
				{
					$b_is_library_object = TRUE;
					$s_category = 'modules';
					$s_element = $s_module;
					$s_file = __DIR__.'/../../'.$s_module.'/central.lib';
					$s_category = trim(file_get_contents($s_file));
					if($s_category == "") $s_category = 'modules';
					$v_central_lib_conf = explode('/', $s_category);
					if(count($v_central_lib_conf)>1)
					{
						$s_category = $v_central_lib_conf[0];
						$s_element = $v_central_lib_conf[1];
					}
				}
				$v_moduledata = array();
				$b_moduledata_found = false;
				$o_query = $o_main->db->query('select * from moduledata where name = ?', array($s_module));
				if($o_query && $o_query->num_rows()>0)
				{
					$b_moduledata_found = true;
					$v_moduledata = $o_query->row_array();
				}
				
				$inputversion = get_folder_version($dir."/".$s_module."/input");
				$moduleversion = get_folder_version($dir."/".$s_module);
				$moduleversion_prefix = substr($moduleversion,0,2);
				if($moduleversion_prefix == "D." || $moduleversion_prefix == "C.") $moduleversion = substr($moduleversion,2);
				?><tr class="<?php echo (!$b_moduledata_found ? "danger" : ($v_moduledata['type'] == 10 ? "warning" : ""));?>">
				<td><?php echo $v_moduledata['uniqueID'];?></td>
				<td class="word-break"><?php echo $s_module;?></td>
				<td>
					<?php if($moduleversion!=''){?><div><?php echo $moduleversion;?>&nbsp;(M)</div><?php } ?>
					<?php if($inputversion!=''){?><div><?php echo $inputversion;?>&nbsp;(I)</div><?php } ?>
				</td>
				<td>
					<div class="mm-change-local adm-ui-checkbox-<?php echo (1 == $v_moduledata['custom_module'] ? 'full':'empty');?>" data-href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=custom_module&sys_module=".$s_module."&_=".$l_random;?>"></div>
				</td>
				<td>
					<div class="mm-change-local adm-ui-checkbox-<?php echo (1 == $v_moduledata['local_module'] ? 'full':'empty');?>" data-href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=local_module&sys_module=".$s_module."&_=".$l_random;?>"></div>
				</td>
				<td class="mm-change-local-name word-break"><span><?php echo $v_moduledata['local_name'];?></span> <span class="glyphicon glyphicon-pencil" data-href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=local_name&sys_module=".$s_module."&_=".$l_random;?>"></span></td>
				<td class="word-break"><?php echo ($b_is_library_object?str_replace($s_module, '', $s_element):'');?></td>
				<td>
					<?php if($b_moduledata_found) { ?>
					<select id="mm_change_module_type" onChange="window.location.href=this.value;"><?php
					$v_modulemodes = array("C"=>"Customer", "E"=>"Extra", "I"=>"Designer", "A"=>"Admin", "S"=>"System admin", "D"=>"Developer");
					foreach($v_modulemodes as $x => $s_mode_name)
					{
						if(strpos("IAS", $x) !== false && $v_moduledata['modulemode']!=$x) continue;
						?><option value="<?php echo $extradir;?>/addOn_include/addlibrary.php?<?php echo $linkStandard;?>&updatemode=<?php echo $s_module;?>&mode=<?php echo $x."&_=".$l_random;?>"<?php echo ($v_moduledata['modulemode']==$x ? ' selected':'');?>><?php echo $s_mode_name;?></option><?php
					}
					?></select>
					<?php } ?>
				</td>
				<td>
					<?php if($b_moduledata_found) { ?>
					<select id="mm_change_module_type" onChange="window.location.href=this.value;"><?php
					$v_moduledatatypes = array(0=>"Content module", 1=>"Menu module", 2=>"Always load output css", 3=>"Email template", 4=>"CRM email template", 10=>"System module", 100=>"Payment module", 101=>"Delivery module", 102=>"Giftcard module", 103=>"Customer module");
					foreach($v_moduledatatypes as $x => $s_type_name)
					{
						?><option value="<?php echo $extradir;?>/addOn_include/addlibrary.php?<?php echo $linkStandard;?>&updatetype=<?php echo $s_module;?>&type=<?php echo $x."&_=".$l_random;?>"<?php echo ($v_module_types[$s_module]==$x ? ' selected':'');?>><?php echo $s_type_name;?></option><?php
					}
					?></select>
					<?php } ?>
				</td>
				<td>
					<div class="btn-group btn-group-xs">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php echo $formText_Action_List;?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<?php
							if($b_is_library_object)
							{
								?>
								<li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=verify_object&verifyobject=sys_module&sys_module=".$s_module."&_=".$l_random;?>"><?php echo $formText_VerifyModule_input;?></a></li>
								<li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=update_object&updateobject=sys_module&sys_module=".$s_module."&_=".$l_random;?>"><?php echo $formText_UpdateModule_input;?></a></li>
								<?php
							}
							?>
							<li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $linkStandard;?>&includefile=updatemodule&dir=<?php echo $s_module;?>&update=<?php echo $s_module."&_=".$l_random;?>"><?php echo $formText_UpdateInput_input;?></a></li>
							<?php if($v_moduledata['type'] != 10 && $s_module != "Modulemanager") { ?><li role="separator" class="divider"></li><li><a onClick="return confirm('<?php echo $formText_AreYouSureYouWantToDelete.': '.$s_module;?>?');" href="<?php echo $extradir;?>/addOn_include/delete.php?<?php echo $linkStandard;?>&deletemodule=<?php echo $s_module."&_=".$l_random;?>"><?php echo $formText_delete_list;?></a></a><?php } ?>
						</ul>
					</div>
				</td>
				<td><?php
				if($b_is_library_object)
				{
					$s_key = 'modules_'.$s_element;
					if(isset($v_locked_elements[$s_key]))
					{
						?><span class="locked-object glyphicon glyphicon-info-sign" data-date="<?php echo $v_locked_elements[$s_key]['lock_date'];?>" data-username="<?php echo $v_locked_elements[$s_key]['lock_username'];?>" data-comment="<?php echo $v_locked_elements[$s_key]['lock_comment'];?>" data-url="<?php echo $extradir.'/addOn_include/addlibrary.php?'.$linkStandard.'&unlock_object='.$s_category.':'.$s_element.'&_='.$l_random;?>"></span><?php
					} else {
						?><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=lock_object&lock_object=sys_module&sys_module=".$s_module."&_=".$l_random;?>"><?php echo $formText_Lock_input;?></a><?php
					}
				}
				?></td>
				</tr><?php
				$counter++;
			}
		}
	}
	?>
	</tbody></table>
	
	<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th><?php echo $formText_Object_input;?></th>
			<th><?php echo $formText_Version_input;?></th>
			<th><?php echo $formText_Edition_input;?></th>
			<th colspan="4"><?php echo $formText_Action_input;?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$b_is_library_object = TRUE;
		$v_objects = array(
			"api"=>$formText_Api_input,
			"ckeditor"=>$formText_CkEditor_input,
			"kcfinder"=>$formText_KcFinder_input,
			/*"lib"=>$formText_AccountLib_input,*/
			"min"=>$formText_Minifier_input/*,
			"elementsGlobal"=>$formText_ElementsGlobal_input*/
		);
		foreach($v_objects as $s_object => $s_title)
		{
			$s_element = $s_object;
			if(!is_dir(__DIR__.'/../../../'.$s_object.'/')) continue;
			if(is_file(__DIR__.'/../../../'.$s_object.'/central.lib'))
			{
				$s_category = 'account_elements';
				$s_file = __DIR__.'/../../../'.$s_object.'/central.lib';
				$s_category = trim(file_get_contents($s_file));
				if($s_category == "") $s_category = 'account_elements';
				$v_central_lib_conf = explode('/', $s_category);
				if(count($v_central_lib_conf)>1)
				{
					$s_category = $v_central_lib_conf[0];
					$s_element = $v_central_lib_conf[1];
				}
			}
			?>
			<tr>
				<td><?php echo $s_title;?></td>
				<td><?php echo get_folder_version(__DIR__."/../../../".$s_object."/");?></td>
				<td><?php echo ($b_is_library_object?str_replace($s_object, '', $s_element):'');?></td>
				<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=verify_object&verifyobject=account_elements&account_elements=".$s_object."&_=".$l_random;?>"><?php echo $formText_VerifyObject_input;?></a></td>
				<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=update_object&updateobject=account_elements&account_elements=".$s_object."&_=".$l_random;?>"><?php echo $formText_UpdateObject_input;?></a></td>
				<td><?php
				if($b_is_library_object)
				{
					$s_key = 'account_elements_'.$s_element;
					if(isset($v_locked_elements[$s_key]))
					{
						?><span class="locked-object glyphicon glyphicon-info-sign" data-date="<?php echo $v_locked_elements[$s_key]['lock_date'];?>" data-username="<?php echo $v_locked_elements[$s_key]['lock_username'];?>" data-comment="<?php echo $v_locked_elements[$s_key]['lock_comment'];?>" data-url="<?php echo $extradir.'/addOn_include/addlibrary.php?'.$linkStandard.'&unlock_object=account_elements:'.$s_element.'&_='.$l_random;?>"></span><?php
					} else {
						?><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=lock_object&lock_object=account_elements&account_elements=".$s_object."&_=".$l_random;?>"><?php echo $formText_Lock_input;?></a><?php
					}
				}
				?></td>
				<td></td>
			</tr>
			<?php
		}
		$s_element = $s_object = 'fw';
		if(is_file(__DIR__.'/../../../'.$s_object.'/central.lib'))
		{
			$s_category = 'fw';
			$s_file = __DIR__.'/../../../'.$s_object.'/central.lib';
			$s_category = trim(file_get_contents($s_file));
			if($s_category == "") $s_category = 'account_elements';
			$v_central_lib_conf = explode('/', $s_category);
			if(count($v_central_lib_conf)>1)
			{
				$s_category = $v_central_lib_conf[0];
				$s_element = $v_central_lib_conf[1];
			}
		}
		?>
		<tr>
			<td><?php echo $formText_Framework_input;?></td>
			<td><?php echo get_folder_version(__DIR__."/../../../fw/");?></td>
			<td><?php echo ($b_is_library_object?str_replace($s_object, '', $s_element):'');?></td>
			<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=verify_object&verifyobject=fw&_=".$l_random;?>"><?php echo $formText_VerifyObject_input;?></a></td>
			<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=update_object&updateobject=fw&_=".$l_random;?>"><?php echo $formText_UpdateObject_input;?></a></td>
			<td><?php
			if($b_is_library_object)
			{
				$s_key = 'frameworks_'.$s_element;
				if(isset($v_locked_elements[$s_key]))
				{
					?><span class="locked-object glyphicon glyphicon-info-sign" data-date="<?php echo $v_locked_elements[$s_key]['lock_date'];?>" data-username="<?php echo $v_locked_elements[$s_key]['lock_username'];?>" data-comment="<?php echo $v_locked_elements[$s_key]['lock_comment'];?>" data-url="<?php echo $extradir.'/addOn_include/addlibrary.php?'.$linkStandard.'&unlock_object=frameworks:'.$s_element.'&_='.$l_random;?>"></span><?php
				} else {
					?><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=lock_object&lock_object=fw&_=".$l_random;?>"><?php echo $formText_Lock_input;?></a><?php
				}
			}
			?></td>
			<td></td>
		</tr>
	</tbody>
	</table>
	
	<?php if(is_dir(BASEPATH."elementsCustomized")) { ?>
	<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th><?php echo $formText_CustomizedElements_input;?></th>
			<th><?php echo $formText_Version_input;?></th>
			<th><?php echo $formText_Edition_input;?></th>
			<th colspan="4"><?php echo $formText_Action_input;?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$b_is_library_object = TRUE;
		$v_objects = scandir(BASEPATH.'elementsCustomized');
		foreach($v_objects as $s_object)
		{
			if('.' == $s_object || '..' == $s_object) continue;
			$s_element = $s_title = $s_object;
			if(!is_dir(BASEPATH.'elementsCustomized/'.$s_object.'/')) continue;
			if(is_file(BASEPATH.'elementsCustomized/'.$s_object.'/central.lib'))
			{
				$s_category = 'customized_elements';
				$s_file = BASEPATH.'elementsCustomized/'.$s_object.'/central.lib';
				$s_category = trim(file_get_contents($s_file));
				if($s_category == "") $s_category = 'customized_elements';
				$v_central_lib_conf = explode('/', $s_category);
				if(count($v_central_lib_conf)>1)
				{
					$s_category = $v_central_lib_conf[0];
					$s_element = $v_central_lib_conf[1];
				}
			}
			?>
			<tr>
				<td><?php echo $s_title;?></td>
				<td><?php echo get_folder_version(BASEPATH.'elementsCustomized/'.$s_object.'/');?></td>
				<td><?php echo ($b_is_library_object?str_replace($s_object, '', $s_element):'');?></td>
				<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=verify_object&verifyobject=customized_elements&customized_elements=".$s_object."&_=".$l_random;?>"><?php echo $formText_VerifyObject_input;?></a></td>
				<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=update_object&updateobject=customized_elements&customized_elements=".$s_object."&_=".$l_random;?>"><?php echo $formText_UpdateObject_input;?></a></td>
				<td><?php
				if($b_is_library_object)
				{
					$s_key = 'customized_elements_'.$s_element;
					if(isset($v_locked_elements[$s_key]))
					{
						?><span class="locked-object glyphicon glyphicon-info-sign" data-date="<?php echo $v_locked_elements[$s_key]['lock_date'];?>" data-username="<?php echo $v_locked_elements[$s_key]['lock_username'];?>" data-comment="<?php echo $v_locked_elements[$s_key]['lock_comment'];?>" data-url="<?php echo $extradir.'/addOn_include/addlibrary.php?'.$linkStandard.'&unlock_object=customized_elements:'.$s_element.'&_='.$l_random;?>"></span><?php
					} else {
						?><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=lock_object&lock_object=customized_elements&customized_elements=".$s_object."&_=".$l_random;?>"><?php echo $formText_Lock_input;?></a><?php
					}
				}
				?></td>
				<td></td>
			</tr>
			<?php
		}
		?>
	</tbody>
	</table>
	<?php } ?>
	
	<div class="text-right">
		<a class="btn btn-default btn-xs optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=compare_accounts&_=".$l_random;?>"><?php echo $formText_CompareAccounts_input;?></a>
		<a class="btn btn-default btn-xs optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=update_all_inputs&_=".$l_random;?>"><?php echo $formText_UpdateAllModuleInputs_input;?></a>
		<a class="btn btn-default btn-xs optimize" href="<?php echo $_SERVER['PHP_SELF']."?".$linkStandard."&includefile=update_all_objects&_=".$l_random;?>"><?php echo $formText_UpdateAllAccountLibraryElements_input;?></a>
		<a class="btn btn-default btn-xs optimize" href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $linkStandard;?>&includefile=put_item_into_library"><?php echo $formText_PutObjectsIntoCentralLibrary_input;?></a>
	</div>
	</div><?php
} else {
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField"><?php echo $formText_YouHaveNoAccessToThisModule_input;?></td></tr></table></div><?php
}


function print_info($message, $image='')
{
	?><div><table width="100%" border="0">
		<tr><?php echo ($image!='' ? '<td><img src="'.$image.'" alt="" border="0" height="35" /></td>' : '');?><td><?php echo $message;?></td></tr>
	</table></div><?php
}
?>
<div id="popupeditbox" class="popupeditbox">
	<span class="button b-close fw_popup_x_color"><span>X</span></span>
	<div id="popupeditboxcontent"></div>
</div>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
        if($(this).hasClass("close-reload")){
            loadView("details", {cid: "<?php echo $cid;?>"});
        }
		$(this).removeClass('opened');
	}
};

$(function(){
	$('span.locked-object').on('click', function(e){
		e.preventDefault();
		fw_info_message_add('info', '<b>Locked on:</b> ' + $(this).data('date') + '&nbsp;&nbsp;&nbsp;<b>Locked by:</b> ' + $(this).data('username') + '<br/><b>Comment:</b> ' + $(this).data('comment') + '<br/><b><a style="color:black;" href="' + $(this).data('url') + '"><?php echo $formText_Unlock_input;?></a></b>', true);
	});
	$('.mm-change-local').on('click', function(e){
		fw_loading_start();
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: $(this).data('href'),
			data: { fwajax: 1, fw_nocss: 1 },
			success: function(json) {
				$('#popupeditboxcontent').html(json.data.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		}).fail(function() {
			sendEmail_alert("<?php echo $formText_ErrorOccurredProcessingRequest_Output;?>", "danger");
			fw_loading_end();
		});
	});
	$('.mm-change-local-name span.glyphicon-pencil').on('click', function(e){
		fw_loading_start();
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: $(this).data('href'),
			data: { fwajax: 1, fw_nocss: 1 },
			success: function(json) {
				$('#popupeditboxcontent').html(json.data.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		}).fail(function() {
			sendEmail_alert("<?php echo $formText_ErrorOccurredProcessingRequest_Output;?>", "danger");
			fw_loading_end();
		});
	});
});
</script>
<style>
.modulecontent { width:900px !important; }
span.locked-object, .mm-change-local { cursor:pointer; }
.mm-change-local-name span.glyphicon-pencil {
	cursor:pointer;
	padding-left:8px;
	color:#46b2e2;
}
/*Popup Start
---------------------------------------- */
.popupeditbox {
	background-color:#FFFFFF;
	border-radius:4px;
	color:#111111;
	display:none;
	padding: 15px 15px 25px;
	width:700px;
	top:50px;
}
.popupeditbox .button {
	background-color:#555D68;
	color:#fff;
	cursor:pointer;
	display:inline-block;
	padding:10px 20px;
	text-align:center;
	text-decoration:none;
}
.popupeditbox .button:hover {
	background-color:#555D68;
}
.popupeditbox .button.b-close,
.popupeditbox .button.bClose {
	position:absolute;
	border: 3px solid #fff;
	-webkit-border-radius: 100px;
	-moz-border-radius: 100px;
	border-radius: 100px;
	padding: 0px 9px;
	font: bold 100% sans-serif;
	line-height: 25px;
	right:-10px;
	top:-10px
}
.popupeditbox .button > span {
	font: bold 100% sans-serif;
	font-size: 12px;
	line-height: 12px;
}
.popupeditbox .popupformTitle {
	font-size: 24px;
	padding: 0px 0px 10px;
	border-bottom: 1px solid #ededed;
	color: #5d5d5d;
	margin-bottom: 15px;
}
.popupeditbox .popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px 5px 0px;
}
.popupeditbox .popupforminput .selectDiv {
	border: 0;
}
.popupeditbox input.popupforminput {
	font-size:12px;
	font-weight:normal;
	border:1px solid #CCCCCC;
	width:100%;
}
.popupeditbox select.popupforminput {
	font-size:12px;
	font-weight:normal;
	border:1px solid #CCCCCC;
	width:100%;
}
.popupeditbox textarea.popupforminput {
	font-size:12px;
	font-weight:normal;
	height:100px;
	border:1px solid #CCCCCC;
	width:100%;
}
.popupeditbox .popupformheader {
	color: #000000;
	font-size: 24px;
	padding:5px 0px 5px 0px;
}
.popupeditbox .popupformdesc {
	color: #000000;
	font-size: 12px;
	padding-bottom:10px;
}
.popupeditbox .popupformbtn {
	text-align:right;
	margin:10px;
}
.popupeditbox .popupformbtn input,
.popupeditbox .popupformbtn button {
	border-radius:4px;
	border:none;
	background-color:#667573;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.popupeditbox .popupformbtn button.b-close {

}
.popupeditbox .lineInput .otherInput {
    margin-top: 10px;
}
.popupeditbox .lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.popupeditbox .lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
}
.popupeditbox .popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupeditbox .popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupeditbox .popupform .lineInput.lineWhole {
	font-size: 14px;
}
.popupeditbox .popupform .lineInput.lineWhole label {
	font-weight: normal !important;
}
.popupeditbox .selectDivModified {
    display:block;
}
.popupeditbox .popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	position:relative;
}
.popupeditbox label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
.popupeditbox .popupform .popupforminput.error { border-color:#c11 !important;}
.popupeditbox #popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }

.popupeditbox .inner {
	padding:10px;
}
.popupeditbox .pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupeditbox .popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupeditbox .popupforminput.botspace {
	margin-bottom:10px;
}
.popupeditbox textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupeditbox .popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupeditbox .popupformbtn {
	text-align:right;
	margin:10px;
}
.popupeditbox .popupformbtn input {
	border-radius:4px;
	border:1px solid #667573;
	background-color:#667573;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.popupeditbox .error {
	border: 1px solid #c11;
}
.popupeditbox .popupform .lineTitle {
	font-weight:700;
}
.popupeditbox .popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupeditbox .popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}
.popupeditbox .popupform .line .lineInput {
	width:70%;
	float:left;
}
/*Popup css END
---------------------------------------- */
</style>