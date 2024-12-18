<?php
ini_set('opcache.revalidate_freq', 0);
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
require_once(__DIR__."/class_Field.php");
require_once(__DIR__."/fn_sys_get_field_config.php");
require_once(__DIR__."/fn_sys_get_field_setting_text.php");

$fieldWork = $blockWork = "";
if(isset($_GET['fieldWork']) && $_GET['fieldWork'] != "") $fieldWork = $_GET['fieldWork'];
if(isset($_POST['module'])) $module = $_POST['module'];
if(isset($_POST['submodule'])) $submodule = $_POST['submodule'];

if(isset($_POST['send']))
{
	$extradir = __DIR__.'/../../';
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	include(__DIR__."/ftp_commands.php");
	include(__DIR__."/readInputLanguage.php");
	include(__DIR__."/config_mysql_reserved_words.php");
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");

	include(__DIR__."/fieldloader.php");
	$file = "/modules/$module/input/settings/fields/".$submodule."fields.php";
	$v_field_config = sys_get_field_config($file, $account_absolute_path, $databases);

	$extra = "";
	$alterData = new Field();
	$_POST['databasename'] = preg_replace('#[^A-za-z0-9_ ]+#', '',trim($_POST['databasename']));
	$tmp = array_map("ucfirst",explode("_",str_replace(" ","_",$_POST['databasename'])));
	$field_language_variable = implode("",$tmp);
	$_POST['databasename'] = str_replace(" ","_",$_POST['databasename']);

	$alterData->init($_POST['databasename'], $submodule."".$_POST['databasename'], '$formText_'.$field_language_variable."_accounts", $_POST['databasetable'], $_POST['fieldtype'], $extra, $_POST['default'], $_POST['hiddenfield'], $_POST['readonlyfield'],$_POST['extravalue'],$_POST['updatefield'],$_POST['insertfield'],$_POST['mandatoryfield'],$_POST['duplicatefield'],$_POST['fieldWidth']);

	$new = 1;
	$error_msg = array();
	foreach($v_field_config as $vab)
	{
		if($fieldWork == $vab->formname)
		{
			$new = 0;
		} else {
			if($vab->formname == $alterData->formname)
			{
				$error_msg["error_".sizeof($error_msg)] = $formText_ThisNameIsAlreadyTaken_input;
			} else if($vab->sqlname == $alterData->sqlname && $vab->database == $alterData->database)
			{
				$error_msg["error_".sizeof($error_msg)] = $formText_ThisDatabaseNameIsAlreadyTaken_input;
			} else if($alterData->type == 20 && $_POST['submoduleconnect'] == "none")
			{
				$error_msg["error_".sizeof($error_msg)] = $formText_MustChooseASubmoduleConnectionForThisTypeOfField_input;
			}
		}
	}
	if(strlen($alterData->sqlname)==0)
	{
		$error_msg["error_".sizeof($error_msg)] = $formText_NameCannotBeEmpty_input;
	}
	if(in_array(strtolower($alterData->sqlname),$reservedWords))
	{
		$error_msg["error_".sizeof($error_msg)] = $formText_FieldWithThisNameIsNotAllowedReservedWord_input;
	}

	if(count($error_msg)==0)
	{
		$v_mandatory_update_types = array('Decimal', 'Dropdown', 'RadioButton', 'RadioButtonAdv');

		//Add comment
		$s_field_comment = '';
		if($alterData->type == 'Dropdown' || $alterData->type == 'RadioButtonAdv')
		{
			$v_extra_values = explode('::',$alterData->extravalue);
			foreach($v_extra_values as $s_extra_value_item)
			{
				$v_extra_value_item = explode(':', $s_extra_value_item);
				if(strpos($v_extra_value_item[1], '$formText') !== FALSE)
				{
					$v_tmp = explode('_', $v_extra_value_item[1]);
					$v_extra_value_item[1] = $v_tmp[1];
				}
				$s_field_comment .= ($s_field_comment!=''?'; ':'').$v_extra_value_item[0].' - '.$v_extra_value_item[1];
			}
		}
		if($alterData->type == 'RadioButton')
		{
			$v_extra_values = explode(':',$alterData->extravalue);
			foreach($v_extra_values as $l_extra_value_key => $s_extra_value_item)
			{
				if(strpos($s_extra_value_item, '$formText') !== FALSE)
				{
					$v_tmp = explode('_', $s_extra_value_item);
					$s_extra_value_item = $v_tmp[1];
				}
				$s_field_comment .= ($s_field_comment!=''?'; ':'').$l_extra_value_key.' - '.$s_extra_value_item;
			}
		}
		if($s_field_comment != '') $s_field_comment = " COMMENT '".str_replace("'", "", $s_field_comment)."'";

		if($new == 0 && $_POST['fieldtype'] == $_POST['oldfieldtype'] && !in_array($alterData->type, $v_mandatory_update_types))
		{
			//no updates in DB
			$alterData->input_desc = $v_field_config[$fieldWork]->input_desc;
			if($v_field_config[$fieldWork]->htmlname != ''){
				$alterData->htmlname = $v_field_config[$fieldWork]->htmlname;
			}
			$v_field_config[$fieldWork] = $alterData;
		} else if($new == 0 && ($_POST['fieldtype'] != $_POST['oldfieldtype'] || in_array($alterData->type, $v_mandatory_update_types))) {
			//alter field in DB
			$alterData->input_desc = $v_field_config[$fieldWork]->input_desc;
			if($v_field_config[$fieldWork]->htmlname != ''){
				$alterData->htmlname = $v_field_config[$fieldWork]->htmlname;
			}
			$v_field_config[$fieldWork] = $alterData;
			$textType = "";

			if(is_file($module_absolute_path."/input/fieldtypes/".$alterData->type."/fielddata.php"))
			{
				include($module_absolute_path."/input/fieldtypes/".$alterData->type."/fielddata.php");
				$textType = $thisDatabaseField;
			}
			//override
			if($alterData->type == 'Decimal' and $alterData->extravalue != "")
			{
				$ex = explode(",",$alterData->extravalue);
				$textType = "DECIMAL(".(intval($ex[0])<1 ? 11 : intval($ex[0])).",".intval($ex[1]).")";
			}
			//echo "HER".$alterData->type." ".$textType."<br />";
			if(is_file($module_absolute_path."/input/fieldtypes/".$alterData->type."/createField.php"))
			{
				include($module_absolute_path."/input/fieldtypes/".$alterData->type."/createField.php");
			}

			if(!$o_main->db->field_exists($alterData->sqlname, $alterData->database))
			{
				$o_main->db->simple_query("ALTER TABLE ".$alterData->database." CHANGE ".$alterData->sqlname." ".$alterData->sqlname." ".$textType.$s_field_comment);
			}
		} else {
			$v_field_config[] = $alterData;
			$textType = "";

			if(is_file($module_absolute_path."/input/fieldtypes/".$alterData->type."/fielddata.php"))
			{
				include($module_absolute_path."/input/fieldtypes/".$alterData->type."/fielddata.php");
				$textType = $thisDatabaseField;
			}

			if(is_file($module_absolute_path."/input/fieldtypes/".$alterData->type."/createField.php"))
			{
				include($module_absolute_path."/input/fieldtypes/".$alterData->type."/createField.php");
			}
			if(!$o_main->db->field_exists($alterData->sqlname, $alterData->database))
			{
				$o_main->db->simple_query("ALTER TABLE ".$alterData->database." ADD COLUMN ".$alterData->sqlname." ".$textType.$s_field_comment);
			}
		}
		$makeString = "";
		foreach($v_field_config as $nut)
		{
			if($makeString != "")
			{
				$makeString .= "\",\"";
			}
			if(strpos($nut->htmlname,'$') !== false and strpos($nut->htmlname,'{') === false) $nut->htmlname = '{'.$nut->htmlname.'}';

			$makeString .= $nut->sqlname."¤".$nut->formname."¤".$nut->htmlname."¤".$nut->database."¤".$nut->type."¤".$nut->extra."¤".$nut->default."¤¤¤".$nut->hidden."¤".$nut->readonly."¤".$nut->extravalue."¤".$nut->update."¤".$nut->insert."¤".$nut->mandatory."¤".$nut->duplicate."¤".$nut->fieldwidth."¤".$nut->input_desc."¤";
		}
		//echo $makeString;
		//exit;

		$outString = "\$prefields = array(\"$makeString\");";
		ftp_file_put_content("modules/$module/input/settings/fields/".$submodule."fields.php","<?php".PHP_EOL.$outString.PHP_EOL."?>");

		$jsScriptFile = "/modules/$module/input/settings/fieldsJS/".$submodule."".$_POST['databasename'].".php";
		if(is_file($account_absolute_path.$jsScriptFile))
		{
			ftp_delete_file($jsScriptFile);
		}
		if($_POST['fieldjs'] != "")
		{
			ftp_file_put_content($jsScriptFile,$_POST['fieldjs']);
		}

	} else {
		$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	}

	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
	exit;
}

if(isset($_POST['savefieldstructure']))
{
	$extradir = __DIR__.'/../../';
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	include(__DIR__."/ftp_commands.php");
	include(__DIR__."/readInputLanguage.php");
	include(__DIR__."/fn_format_form_variable.php");
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");

	$do = $is_block = $is_group = false;
	$data = $fields = $free_fields = array();
	$uid = $block_id = $group_id = 0;
	foreach($_POST as $key => $value)
	{
		if($key == 'starts_structure') $do = true;

		if($do && strpos($key,'field_name_') !== false)
		{
			$fields[$value] = 1;
		}
		if($do && strpos($key,'group_columns_') !== false)
		{
			if(sizeof($fields)>0) {
				if($is_block) {
					$data[$block_id]['sys_childs'] = array_merge($data[$block_id]['sys_childs'],$fields);
				} else {
					$free_fields = array_merge($free_fields,$fields);
					$data = array_merge($data, $fields);
				}
				$fields = array();
			}
			$uid++;
			$group_id = $uid;
			$is_group = true;
			if($is_block) {
				$data[$block_id]['sys_childs'][$group_id]['sys_columns'] = ($value > 0 ? $value : 1);
				$data[$block_id]['sys_childs'][$group_id]['sys_childs'] = array();
			} else {
				$data[$group_id]['sys_columns'] = ($value > 0 ? $value : 1);
				$data[$group_id]['sys_childs'] = array();
			}
		}
		if($do && strpos($key,'block_name_') !== false)
		{
			if(sizeof($fields)>0) {
				$free_fields = array_merge($free_fields,$fields);
				$data = array_merge($data, $fields);
				$fields = array();
			}
			$uid++;
			$block_id = $uid;
			$is_block = true;
			$data[$block_id]['sys_name'] = format_form_variable($value,"settingBlocks");
			$data[$block_id]['sys_childs'] = array();
		}
		if($do && strpos($key,'block_collapse_') !== false)
		{
			$data[$block_id]['sys_collapse'] = $value;
		}
		if($do && strpos($key,'group_end_') !== false)
		{
			if($is_block)
			{
				$data[$block_id]['sys_childs'][$group_id]['sys_childs'] = $fields;
			} else {
				$data[$group_id]['sys_childs'] = $fields;
			}
			$fields = array();
			$is_group = false;
		}
		if($do && strpos($key,'block_end_') !== false)
		{
			if(sizeof($fields)>0) {
				$data[$block_id]['sys_childs'] = array_merge($data[$block_id]['sys_childs'],$fields);
				$fields = array();
			}
			$is_block = false;
		}
		if($do && $key == 'starts_freefields')
		{
			if(sizeof($fields)>0) {
				$fields = array_merge($free_fields,$fields);
			}
			$uid++;
			$block_id = $uid;
			$is_block = true;
		}

		if($key == 'savefieldstructure')
		{
			$data = array_merge($data, $fields);
			break;
		}
	}

	$b_found = FALSE;
	$s_file_content = var_export($data, TRUE);
	for($i=0;$i<strlen($s_file_content);$i++)
	{
		if($s_file_content[$i] == '$' && substr($s_file_content,$i,5)=='$form')
		{
			$b_found = TRUE;
			$s_file_content = substr_replace($s_file_content, '', $i-1, 1);
			$i--;
		}
		if($b_found && $s_file_content[$i] == "'")
		{
			$b_found = FALSE;
			$s_file_content = substr_replace($s_file_content, '', $i, 1);
			$i--;
		}
	}

	ftp_file_put_content("modules/$module/input/settings/blocks/".$submodule.".php","<?php\n\$preblocks = ".$s_file_content.";\n?>");

	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
	exit;
}

$file = "/modules/$module/input/settings/fields/".$submodule."fields.php";
$v_field_config = sys_get_field_config($file, $account_absolute_path, $databases);
if(is_file(__DIR__."/../settings/blocks/".$submodule.".php")) include(__DIR__."/../settings/blocks/".$submodule.".php");


if(isset($_GET['editOrder']))
{
	include($file);
	$filearray = $prefields;
	$fromRelations = 0;
	$arrayname = "prefields";
	include($module_absolute_path."/input/includes/editFileArrayOrder.php");

} else if($fieldWork == "") {
	?>
	<div class="module-manager">
	<h2><?php echo $formText_FieldsInModule_input.': '.$module.'('.$submodule.')';?></h2>
	<div class="content_buttons">
		<a class="btn btn-primary btn-sm optimize" role="button" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editFieldSettings&fieldWork=newfield";?>"><?php echo $formText_NewField_input;?></a>
		<button id="edit_add_block" class="btn btn-info btn-sm" type="button" role="button"><?php echo $formText_NewBlock_input;?></button>
		<button id="edit_add_group" class="btn btn-warning btn-sm" type="button" role="button"><?php echo $formText_NewGroup_input;?></button>
	</div>
	<form action="<?php echo $extradir."/input/includes/".$_GET['includefile'].".php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editFieldSettings";?>" method="post">
	<input type="hidden" name="submodule" value="<?php echo $submodule;?>" />
	<input type="hidden" name="module" value="<?php echo $module;?>" />
	<input type="hidden" name="moduleID" value="<?php echo $moduleID;?>" />
	<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>" />
	<input type="hidden" name="extradir" value="<?php echo $extradir;?>" />
	<input type="hidden" name="parentdir" value="<?php echo $parentdir;?>" />
	<input type="hidden" name="choosenListInputLang" value="<?php echo $choosenListInputLang;?>">
	<input type="hidden" name="choosenAdminLang" value="<?php echo $choosenAdminLang;?>">
	<input type="hidden" name="languageID" value="<?php echo $choosenInputLang;?>" />
	<input type="hidden" name="extraimagedir" value="<?php echo $extraimagedir;?>" />
	<input type="hidden" name="starts_structure" value="1" />
	<div id="blocks_fields">
		<ul id="sort_blocks">
		<?php
		$counter = 1;
		$free_fields = array_keys($fieldsStructure);
		$edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editFieldSettings&fieldWork=";
		$delete_link = $extradir."/input/includes/deletesetting.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editFieldSettings&deleteField=";
		foreach($preblocks as $fieldid => $block)
		{
			if(is_array($block) && isset($block['sys_name']))
			{
				?><li class="ui-state-default is-block"><div class="title row"><div class="col-md-2"><?php echo $formText_BlockTitle_input;?>:</div><div class="col-md-4"><input type="text" name="block_name_<?php echo $counter;?>" value="<?php echo $block['sys_name'];?>"></div><div class="col-md-2"><?php echo $formText_Collapse_input;?>:</div><div class="col-md-2"><select name="block_collapse_<?php echo $counter;?>"><option value="1"><?php echo $formText_Yes_input;?></option><option value="0"<?php echo ($block['sys_collapse']==0?" selected":"");?>><?php echo $formText_No_input;?></option></select></div><div class="buttons col-md-2 text-right"><a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a><a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a><a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a></div></div><ul class="sort_block bg-info"><?php
				foreach($block['sys_childs'] as $fieldid => $item)
				{
					if(isset($item['sys_childs']) && is_array($item['sys_childs']) && sizeof($item['sys_childs'])>0)
					{
						// group
						?><li class="ui-state-default is-group"><div class="title row"><div class="col-md-3"><?php echo $formText_GroupColumnCount_input;?>:</div><div class="col-md-7"><input type="text" name="group_columns_<?php echo $counter;?>" value="<?php echo $item['sys_columns'];?>"></div><div class="buttons col-md-2 text-right"><a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a><a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a><a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a></div></div><ul class="sort_group bg-warning"><?php
						foreach($item['sys_childs'] as $fieldid2 => $item2)
						{
							// field
							$idx = array_search($fieldid2, $free_fields);
							if($idx !== false) unset($free_fields[$idx]);
							else continue;
							$s_field_text = sys_get_field_setting_text($fieldid2, $fieldsStructure, $submodule);
							?><li class="ui-state-default is-field">
								<?php echo $s_field_text;?><input type="hidden" name="field_name_<?php echo $fieldid2;?>" value="<?php echo $fieldid2;?>" />
								<div class="buttons"><a class="btn btn-xs optimize" href="<?php echo $edit_link.$fieldsStructure[$fieldid2][1];?>"><span class="glyphicon glyphicon-edit"></span></a><a class="btn btn-xs delete-field" href="<?php echo $delete_link.$fieldsStructure[$fieldid2][1];?>"><span class="glyphicon glyphicon-trash"></span></a></div>
							</li><?php
						}
						?></ul>
						<input type="hidden" name="group_end_<?php echo $counter;?>" value="1" />
						</li><?php
						$counter++;
					} else {
						// field
						$idx = array_search($fieldid, $free_fields);
						if($idx !== false) unset($free_fields[$idx]);
						else continue;
						$s_field_text = sys_get_field_setting_text($fieldid, $fieldsStructure, $submodule);
						?><li class="ui-state-default is-field fw_clear_both">
							<?php echo $s_field_text;?><input type="hidden" name="field_name_<?php echo $fieldid;?>" value="<?php echo $fieldid;?>" />
							<div class="buttons"><a class="btn btn-xs optimize" href="<?php echo $edit_link.$fieldsStructure[$fieldid][1];?>"><span class="glyphicon glyphicon-edit"></span></a><a class="btn btn-xs delete-field" href="<?php echo $delete_link.$fieldsStructure[$fieldid][1];?>"><span class="glyphicon glyphicon-trash"></span></a></div>
						</li><?php
					}
				}
				?></ul>
				<input type="hidden" name="block_end_<?php echo $counter;?>" value="1" />
				</li><?php
				$counter++;
			} else if(is_array($block) && isset($block['sys_columns']))
			{
				// group
				?><li class="ui-state-default is-group"><div class="title row"><div class="col-md-3"><?php echo $formText_GroupColumnCount_input;?>:</div><div class="col-md-7"><input type="text" name="group_columns_<?php echo $counter;?>" value="<?php echo $block['sys_columns'];?>"></div><div class="buttons col-md-2 text-right"><a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a><a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a><a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a></div></div><ul class="sort_group bg-warning"><?php
				foreach($block['sys_childs'] as $fieldid2 => $item2)
				{
					// field
					$idx = array_search($fieldid2, $free_fields);
					if($idx !== false) unset($free_fields[$idx]);
					else continue;
					$s_field_text = sys_get_field_setting_text($fieldid2, $fieldsStructure, $submodule);
					?><li class="ui-state-default is-field">
						<?php echo $s_field_text;?><input type="hidden" name="field_name_<?php echo $fieldid2;?>" value="<?php echo $fieldid2;?>" />
						<div class="buttons"><a class="btn btn-xs optimize" href="<?php echo $edit_link.$fieldsStructure[$fieldid2][1];?>"><span class="glyphicon glyphicon-edit"></span></a><a class="btn btn-xs delete-field" href="<?php echo $delete_link.$fieldsStructure[$fieldid2][1];?>"><span class="glyphicon glyphicon-trash"></span></a></div>
					</li><?php
				}
				?></ul>
				<input type="hidden" name="group_end_<?php echo $counter;?>" value="1" />
				</li><?php
				$counter++;
			} else {
				// field
				$idx = array_search($fieldid, $free_fields);
				if($idx !== false) unset($free_fields[$idx]);
				else continue;
				$s_field_text = sys_get_field_setting_text($fieldid, $fieldsStructure, $submodule);
				?><li class="ui-state-default is-field fw_clear_both">
					<?php echo $s_field_text;?><input type="hidden" name="field_name_<?php echo $fieldid;?>" value="<?php echo $fieldid;?>" />
					<div class="buttons"><a class="btn btn-xs optimize" href="<?php echo $edit_link.$fieldsStructure[$fieldid][1];?>"><span class="glyphicon glyphicon-edit"></span></a><a class="btn btn-xs delete-field" href="<?php echo $delete_link.$fieldsStructure[$fieldid][1];?>"><span class="glyphicon glyphicon-trash"></span></a></div>
				</li><?php
			}
		}
		foreach($free_fields as $fieldid)
		{
			$s_field_text = sys_get_field_setting_text($fieldid, $fieldsStructure, $submodule);
			?><li class="ui-state-default is-field fw_clear_both">
				<?php echo $s_field_text;?><input type="hidden" name="field_name_<?php echo $fieldid;?>" value="<?php echo $fieldid;?>" />
				<div class="buttons"><a class="btn btn-xs optimize" href="<?php echo $edit_link.$fieldsStructure[$fieldid][1];?>"><span class="glyphicon glyphicon-edit"></span></a><a class="btn btn-xs delete-field" href="<?php echo $delete_link.$fieldsStructure[$fieldid][1];?>"><span class="glyphicon glyphicon-trash"></span></a></div>
			</li><?php
		}
		?>
		</ul>
	</div>
	<div class="content_buttons">
		<input class="btn btn-success btn-ms" type="submit" name="savefieldstructure" value="<?php echo $formText_save_input;?>" />
		<a class="btn btn-default btn-ms optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=chooseToUpdate";?>"><?php echo $formText_Cancel_input;?></a>
	</div>
	</form>
	<style>
	#sort_blocks, .sort_block, .sort_group {
		border:1px solid #666;
		min-height:20px;
		list-style-type:none;
		margin:0;
		padding:10px 0 5px 0;
	}
	#sort_blocks {
		width:95%;
		border:3px dashed #666;
	}
	.sort_group.disable {
		background-color:#444;
	}
	#sort_blocks .title {
		margin-bottom:5px;
		font-weight:bold;
	}
	#sort_blocks .title, #sort_blocks .title a {
		color:#000000;
	}
	#sort_blocks li {
		margin:0 10px 10px 10px;
		padding:10px;
		cursor:move;
		font-weight:normal;
	}
	#sort_blocks li .buttons {
		float:right;
	}
	#sort_blocks li.is-field {
		margin:0 10px 5px 10px;
		padding:0px 5px;
	}
	#sort_blocks li.is-field:hover {
		color:#333333;
		background:none;
		background-color:#46b2e2;
		border-color:#000000;
	}
	</style>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	var fw_field_counter = parseInt('<?php echo $counter;?>');
	$(function() {
		refresh_sortable()
		$("#edit_add_block").off("click").on("click", function() {
			fw_changes_made = true;
			fw_field_counter++;
			$('#sort_blocks').append('<li class="ui-state-default is-block"><div class="title row"><div class="col-md-2"><?php echo $formText_BlockTitle_input;?>:</div><div class="col-md-4"><input type="text" name="block_name_'+fw_field_counter+'" value=""></div><div class="col-md-2"><?php echo $formText_Collapse_input;?>:</div><div class="col-md-2"><select name="block_collapse_'+fw_field_counter+'"><option value="1"><?php echo $formText_Yes_input;?></option><option value="0"><?php echo $formText_No_input;?></option></select></div><div class="buttons col-md-2 text-right"><a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a><a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a><a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a></div></div><ul class="sort_block bg-info"></ul><input type="hidden" name="block_end_'+fw_field_counter+'" value="1" /></li>');
			refresh_sortable();
		});
		$("#edit_add_group").off("click").on("click", function() {
			fw_changes_made = true;
			fw_field_counter++;
			$('#sort_blocks').append('<li class="ui-state-default is-group"><div class="title row"><div class="col-md-3"><?php echo $formText_GroupColumnCount_input;?>:</div><div class="col-md-7"><input type="text" name="group_columns_'+fw_field_counter+'" value="1"></div><div class="buttons col-md-2 text-right"><a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a><a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a><a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a></div></div><ul class="sort_group bg-warning"></ul><input type="hidden" name="group_end_'+fw_field_counter+'" value="1" /></li>');
			refresh_sortable();
		});
	});
	function refresh_sortable()
	{
		//block and group button action
		$("#sort_blocks li .title .buttons .btn").off("click").on("click", function(e) {
			e.preventDefault();
			if($(this).is(".btn_expand")) {
				$(this).addClass("hide").closest("li").children("ul").slideDown("fast");
				$(this).parent().find(".btn_collapse").removeClass("hide");
			}
			if($(this).is(".btn_collapse")) {
				$(this).addClass("hide").closest("li").children("ul").slideUp("fast");
				$(this).parent().find(".btn_expand").removeClass("hide");
			}
			if($(this).is(".delete")) {
				if(!fw_click_instance)
				{
					fw_click_instance = true;
					var _replace = "";
					$block = $(this).closest("li");
					bootbox.confirm({
						message:"<?php echo $formText_DeleteItem_input;?>?",
						buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
						callback: function(result){
							if(result)
							{
								fw_changes_made = true;
								$block.children("ul").each(function(){ _replace = _replace + $(this).html(); });
								$block.replaceWith(_replace);
								refresh_sortable();
							}
							fw_click_instance = false;
						}
					});
				}
			}
		});
		$("#sort_blocks .delete-field").off("click").on("click",function(e){
			e.preventDefault();
			if(!fw_changes_made && !fw_click_instance)
			{
				fw_click_instance = true;
				var $_this = $(this);
				bootbox.confirm({
					message:"<?php echo $formText_DeleteItem_input;?>: " + $(this).closest('li').text().trim() + "?",
					buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
					callback: function(result){
						if(result)
						{
							window.location = $_this.attr("href");
						}
						fw_click_instance = false;
					}
				});
				return false;
			}
			return false;
		});
		$(".sort_block").sortable({
			connectWith: "ul",
			items: "> li",
			cancel: ".buttons, input, select",
			stop: function(event, ui) {
				if((ui.item.hasClass("is-block") || ui.item.hasClass("is-group")) && $(ui.item).parent().closest('li').hasClass("is-group"))
				{
					$(this).sortable("cancel");
				} else {
					fw_changes_made = true;
				}
			}
		});
		$(".sort_group").sortable({
			connectWith: "ul",
			items: "> li",
			cancel: ".buttons, input, select",
			stop: function(event, ui) {
				if((ui.item.hasClass("is-block") || ui.item.hasClass("is-group")) && $(ui.item).parent().closest('li').hasClass("is-group"))
				{
					$(this).sortable("cancel");
				} else {
					fw_changes_made = true;
				}
			}
		});
		$("#sort_blocks").sortable({
			connectWith: "ul",
			items: "> li",
			cancel: ".buttons, input, select",
			stop: function(event, ui) {
				if(
					(
						(ui.item.hasClass("is-block") || ui.item.hasClass("is-group"))
						&& $(ui.item).parent().closest('li').hasClass("is-group")
					) || (
						ui.item.hasClass("is-block") && $(ui.item).parent().closest('li').hasClass("is-block")
					)
				)
				{
					$(this).sortable("cancel");
				} else {
					fw_changes_made = true;
				}
			}
		});
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	<?php

} else {
	$uid = uniqid();
	?><form action="<?php echo $extradir."/input/includes/".$_GET['includefile'].".php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editFieldSettings&fieldWork=".$fieldWork;?>" method="post">
	<input type="hidden" name="submodule" value="<?php echo $submodule;?>" />
	<input type="hidden" name="module" value="<?php echo $module;?>" />
	<input type="hidden" name="moduleID" value="<?php echo $moduleID;?>" />
	<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>" />
	<input type="hidden" name="extradir" value="<?php echo $extradir;?>" />
	<input type="hidden" name="parentdir" value="<?php echo $parentdir;?>" />
	<input type="hidden" name="choosenListInputLang" value="<?php echo $choosenListInputLang;?>">
	<input type="hidden" name="choosenAdminLang" value="<?php echo $choosenAdminLang;?>">
	<input type="hidden" name="languageID" value="<?php echo $choosenInputLang;?>" />
	<input type="hidden" name="extraimagedir" value="<?php echo $extraimagedir;?>" />
	<?php
	ob_start();
	$info_idx = 0;
	$typeDirFiles = $field_info = array();
	$fieldtypeDir = opendir($module_absolute_path."/input/fieldtypes");
	while($typeDirFile = readdir())
	{
		if($typeDirFile != "." && $typeDirFile != "..")
		{
			$typeDirFiles[] = $typeDirFile;
		}
	}
	sort($typeDirFiles);
	reset($typeDirFiles);
	$l_counter=0;
	foreach($typeDirFiles as $i => $typeDirFile)
	{
		$thisAboutInfo = "";
		include($module_absolute_path."/input/fieldtypes/$typeDirFile/fielddata.php");
		if($thisShowOnList == 1)
		{
			$selected = false;
			if($v_field_config[$fieldWork]->type==$typeDirFile || (!isset($v_field_config[$fieldWork]->type) && $l_counter==0))
			{
				$selected = true;
				$info_idx = $l_counter;
			}
			$field_info[$l_counter] = /*$thisFieldType.' - '.*/$thisAboutInfo;
			?><option value="<?php echo $typeDirFile;?>"<?php echo ($selected?" selected":"");?>><?php echo $typeDirFile;?></option><?php
			$l_counter++;
		}
	}
	closedir($fieldtypeDir);
	$ob_fieldtype_options = ob_get_clean();
	?>
	<div id="field_info_lib<?php echo $uid;?>" style="display:none;"><?php
	foreach($field_info as $i => $info)
	{
		?><div class="info<?php echo $i;?>"><?php echo $info;?></div><?php
	}
	?></div>
	<table border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td class="fieldname" width="150"><?php echo $formText_NameInDatabaseTable_input;?></td>
	<td><input name="databasename" type="text"<?php echo ($v_field_config[$fieldWork]->sqlname!=''?' readonly="readonly"':'');?> value="<?php echo $v_field_config[$fieldWork]->sqlname;?>" /></td>
	<td rowspan="10" valign="top">
		<div id="field_info<?php echo $uid;?>" style="padding:3px; margin:3px; border:1px solid #A00; background-color:#EEEEEE; font-size:10px;"><?php echo $field_info[$info_idx];?></div>
		<div id="dbfield_info<?php echo $uid;?>" style="padding:3px; margin:3px; border:1px solid #A00; background-color:#EEEEEE; font-size:10px;"><?php
		if($v_field_config[$fieldWork]->sqlname != '')
		{
			$found = false;
			$o_fields = $o_main->db->field_data($v_field_config[$fieldWork]->database);
			/* get column metadata */
			print '<table><tr><td colspan="2"><strong>'.$formText_MysqlCreatedFieldInfo_input.'</strong></td></tr>';
			if($o_fields)
			{
				foreach($o_fields as $meta)
				{
					if($meta->name == $v_field_config[$fieldWork]->sqlname)
					{
						$found = true;
						if($meta->name!= '')
							echo "<tr><td>".$formText_Name_input.":</td><td>$meta->name</td></tr>";
						if($meta->type != '')
							echo "<tr><td>".$formText_Type_input.":</td><td>$meta->type</td></tr>";
						if($meta->blob != '')
							echo "<tr><td>".$formText_Blob_input.":</td><td>$meta->blob</td></tr>";
						if($meta->max_length != '')
							echo "<tr><td>".$formText_MaxLength_input.":</td><td>$meta->max_length</td></tr>";
						if($meta->primary_key != '')
							echo "<tr><td>".$formText_PrimaryKey_input.":</td><td>$meta->primary_key</td></tr>";
						if($meta->multiple_key != '')
							echo "<tr><td>".$formText_MultipleKey_input.":</td><td>$meta->multiple_key</td></tr>";
						if($meta->not_null != '')
							echo "<tr><td>".$formText_NotNull_input.":</td><td>$meta->not_null</td></tr>";
						if($meta->numeric != '')
							echo "<tr><td>".$formText_Numeric_input.":</td><td>$meta->numeric</td></tr>";
						if($meta->table != '')
							echo "<tr><td>".$formText_Table_input.":</td><td>$meta->table</td></tr>";
						if($meta->unique_key != '')
							echo "<tr><td>".$formText_UniqueKey_input.":</td><td>$meta->unique_key</td></tr>";
						if($meta->unsigned != '')
							echo "<tr><td>".$formText_Unsigned_input.":</td><td>$meta->unsigned</td></tr>";
						if($meta->zerofill != '')
							echo "<tr><td>".$formText_Zerofill_input.":</td><td>$meta->zerofill</td></tr>";
					}
				}
			}
			if (!$found) echo $formText_NoInformationAvailable_input."<br />\n";
			echo "</table>";
		}
		?></div>
	</td>
	</tr>
	<tr><td class="fieldname"><?php echo $formText_DatabaseTable_input;?></td><td>
	<select name="databasetable">
	<?php
	$multiOut = array($formText_OneLanguageOnly_input, $formText_MultiLanguage_input);
	foreach($databases as $base)
	{
		?><option value="<?php echo $base->name;?>"<?php echo ($base->name == $v_field_config[$fieldWork]->database?" selected":"");?>><?php echo $base->name." : ".$multiOut[$base->multilanguage]; ?></option><?php
	}
	?>
	</select></td></tr>
	<tr>
	<td class="fieldname"><?php echo $formText_FieldType_input;?></td>
	<td><input type="hidden" name="oldfieldtype" value="<?php echo $v_field_config[$fieldWork]->type; ?>" />
	<select name="fieldtype" id="fieldtypeholder<?php echo $uid;?>"><?php echo $ob_fieldtype_options;?></select>
	</td></tr>
	<tr><td class="fieldname"><?php echo $formText_HiddenField_input;?></td><td>
		<select name="hiddenfield">
		<option value="0"<?php echo ($v_field_config[$fieldWork]->hidden==0?" selected":"");?>><?php echo $formText_no_input;?></option>
		<option value="1"<?php echo ($v_field_config[$fieldWork]->hidden==1?" selected":"");?>><?php echo $formText_yes_input;?></option>
		</select>
	</td></tr>
	<tr><td class="fieldname"><?php echo $formText_FieldIsReadOnly_input;?></td><td>
		<select name="readonlyfield">
		<option value="0"<?php echo ($v_field_config[$fieldWork]->readonly==0?" selected":"");?>><?php echo $formText_no_input;?></option>
		<option value="1"<?php echo ($v_field_config[$fieldWork]->readonly==1?" selected":"");?>><?php echo $formText_yes_input;?></option>
		</select>
	</td></tr>
	<tr><td class="fieldname"><?php echo $formText_DefaultValue_input;?></td><td>
		<input name="default" type="text" value="<?php echo $v_field_config[$fieldWork]->default;?>" />
	</td></tr>
	<tr><td class="fieldname"><?php echo $formText_ExtraValue_input;?></td><td>
		<input name="extravalue" type="text" id="extravalue<?php echo $uid;?>" value="<?php echo $v_field_config[$fieldWork]->extravalue;?>" />
	</td></tr>
	<tr><td class="fieldname"><?php echo $formText_FieldWidth_input;?></td><td>
		<input name="fieldWidth" type="text" value="<?php echo $v_field_config[$fieldWork]->fieldwidth;?>" />
	</td></tr>
	<?php
	if(1 == 2)
	{
		?><tr><td class="fieldname"><?php echo $formText_FieldUsedInUpdateQueries_input.': '.$v_field_config[$fieldWork]->update; ?></td><td>
			<select name="updatefield">
			<option value="1"<?php echo ($v_field_config[$fieldWork]->update==1?" selected":"");?>><?php echo $formText_yes_input;?></option>
			<option value="0"<?php echo ($v_field_config[$fieldWork]->update==0 && $fieldWork!="newfield"?" selected":"");?>><?php echo $formText_no_input;?></option>
			</select>
		</td></tr>
		<tr><td class="fieldname"><?php echo $formText_FieldUsedInInsertQueries_input;?></td><td>
			<select name="insertfield">
			<option value="1"<?php echo ($v_field_config[$fieldWork]->insert==1?" selected":"");?>><?php echo $formText_yes_input;?></option>
			<option value="0"<?php echo ($v_field_config[$fieldWork]->insert==0 && $fieldWork!="newfield"?" selected":"");?>><?php echo $formText_no_input;?></option>
			</select>
		</td></tr><?php
	} else {
		if($fieldWork == "newfield")
		{
			?>
			<input name="updatefield" value="1" type="hidden" />
			<input name="insertfield" value="1" type="hidden" />
			<?php
		} else {
			?>
			<input name="updatefield" value="<?php echo $v_field_config[$fieldWork]->update;?>" type="hidden" />
			<input name="insertfield" value="<?php echo $v_field_config[$fieldWork]->insert;?>" type="hidden" />
			<?php
		}
	}
	?>
	<tr><td class="fieldname"><?php echo $formText_FieldIsMandatory_input;?></td><td>
		<select name="mandatoryfield">
		<option value="0"<?php echo ($v_field_config[$fieldWork]->mandatory==0?" selected":"");?>><?php echo $formText_no_input;?></option>
		<option value="1"<?php echo ($v_field_config[$fieldWork]->mandatory==1?" selected":"");?>><?php echo $formText_yes_input;?></option>
		</select>
	</td></tr>
	<tr><td class="fieldname"><?php echo $formText_PreventDuplicationOfField_input;?></td><td>
		<select name="duplicatefield">
		<option value="0"<?php echo ($v_field_config[$fieldWork]->duplicate==0?" selected":"");?>><?php echo $formText_no_input;?></option>
		<option value="1"<?php echo ($v_field_config[$fieldWork]->duplicate==1?" selected":"");?>><?php echo $formText_yes_input;?></option>
		</select>
	</td></tr>
	<tr><td colspan="3" class="fieldname"><?php echo $formText_Javascript_input;?></td></tr>
	<tr><td colspan="3">
	<textarea style="width:100%; height:150px;" name="fieldjs"><?php
	$jsScriptFile = "../modules/$module/input/settings/fieldsJS/".$v_field_config[$fieldWork]->formname.".php";
	if(is_file($jsScriptFile))
	{
		print file_get_contents($jsScriptFile);
	}
	?></textarea></td></tr>
	</table>
	<div class="content_buttons">
		<input class="btn btn-success btn-ms" type="submit" name="send" value="<?php echo $formText_save_input;?>" />
		<a class="btn btn-default btn-ms optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=editFieldSettings";?>"><?php echo $formText_Cancel_input;?></a>
	</div>
	</form>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	$(function(){
		$('#fieldtypeholder<?php echo $uid;?>').on("change",function() {
			$('#field_info<?php echo $uid;?>').html($('#field_info_lib<?php echo $uid;?> .info'+$(this).find('option:selected').index()).html());
			$(window).trigger('resize');
		});
	});
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script><?php
}
