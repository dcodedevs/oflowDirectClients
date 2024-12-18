<?php
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
if(!function_exists("ftp_file_put_content")) require_once(__DIR__."/ftp_commands.php");
if(isset($_POST['send']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	$file = $module_absolute_path."/input/settings/relations/".$_GET['submodule'].".php";
	$savefile = "modules/".$_GET['module']."/input/settings/relations/".$_GET['submodule'].".php";

	if(!is_file($file)) ftp_file_put_content($savefile,'<?php'.PHP_EOL.'$prerelations = array();'.PHP_EOL.'?>');
	include($file);

	$s_relation_lang_variable = "";
	$_POST['relation_name'] = preg_replace('#[^A-za-z0-9_ ]+#', '',trim($_POST['relation_name']));
	if($_POST['relation_name']!="")
	{
		$tmp = array_map("ucfirst",explode("_",str_replace(" ","_",$_POST['relation_name'])));
		$s_relation_lang_variable = '{$formText_'.implode("",$tmp).'_Relation}';
	}

	$moduleInfoSplit = explode(":",$_POST['choosenmoduletable']);
	$newFile = $_POST['choosenparentfield']."¤".$moduleInfoSplit[2]."¤".$moduleInfoSplit[1]."¤".$_POST['choosenmodulefield']."¤1¤".$_POST['newbutton']."¤".$_POST['showbutton']."¤".$_POST['choosennamefield']."¤".$_POST['contentstatusfilter']."¤".$s_relation_lang_variable."¤".$_POST['items_per_page']."¤".$_POST['expand_list']."¤".$_POST['in_hidden_list']."¤".$_POST['relation_link_to_module_id']."¤";

	if($_GET['relationedit'] > -1)
		$prerelations[$_GET['relationedit']] = $newFile;
	else
		$prerelations[] = $newFile;

	$newFile = '$prerelations = array(';
	while(list($x,$rest) = each($prerelations))
	{
		$newFile .= '"'.$rest.'",';
	}
	$newFile = substr($newFile,0,strlen($newFile) -1);
	$newFile .=")";

	ftp_file_put_content($savefile,"<?php\n{$newFile};\n?>");

	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=editRelations");
	exit;
}

if(isset($_GET['deleteRelation']))
{
	$file = "/modules/".$_GET['module']."/input/settings/relations/".$_GET['submodule'].".php";
	include($account_absolute_path.$file);
	$savefile = "modules/".$module."/input/settings/relations/".$submodule.".php";
	array_splice($prerelations,$_GET['deleteRelation'],1);
	$newFile = '<?php'.PHP_EOL.'$prerelations = array(';
	while(list($x,$rest) = each($prerelations))
	{
		$newFile .= '"'.$rest.'",';
	}
	if(count($prerelations)>0) $newFile = substr($newFile,0,strlen($newFile) -1);
	$newFile .=')'.PHP_EOL.'?>';

	ftp_file_put_content($savefile,$newFile);
}

if(isset($_GET['editorder']))
{
	$file = "/modules/".$_GET['module']."/input/settings/relations/".$_GET['submodule'].".php";
	include($account_absolute_path.$file);
	$filearray = $prerelations;
	$fromRelations = 1;
	$arrayname = "prerelations";
	include(__DIR__."/editFileArrayOrder.php");
} else {
	?><div style="font-size:16px;"><?php echo $formText_RelationsWith_input.' '.$module.'('.$submodule.') '.$formText_asParent_input;?></div><?php
	if(!isset($_GET['relationedit']) && !isset($_GET['editorder']))
	{
		$kols = array("#f6f7f8","#FFFFFF");
		$file = $module_absolute_path."/input/settings/relations/".$submodule.".php";
		include($file);
		?>
		<div class="module-manager">
		<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editRelations&relationedit=-1";?>"><?php echo $formText_NewRelation_input;?></a><br /><br />
		<table class="table table-condensed table-hover">
		<thead>
		<tr>
			<th><?php echo $formText_Name_Input;?></th>
			<th><?php echo $formText_Relation_Input;?></th>
			<th colspan="3"><?php echo $formText_Action_Input;?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$counter = 0;
		while(list($name,$value) = each($prerelations))
		{
			$relationValue = explode("¤",$value);
			?>
			<tr>
				<td><?php echo $relationValue[9];?></td>
				<td><?php echo $relationValue[0]."->".$relationValue[1]." (".$relationValue[2].":".$relationValue[3].")"; ?></td>
				<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editRelations&relationedit=".$name;?>"><?php echo $formText_EditRelation_input;?></a></td>
				<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editRelations&editorder=1";?>"><?php echo $formText_EditOrder_input;?></a></td>
				<td><a class="delete_relation" data-name="<?php echo $relationValue[0]."->".$relationValue[1]." (".$relationValue[2].":".$relationValue[3].")";?>" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editRelations&deleteRelation=".$name;?>"><?php echo $formText_Delete_input;?></a></td>
			</tr>
			<?php
			$counter++;
		}
		?>
		</tbody>
		</table>
		<div class="content_buttons">
			<a class="btn btn-default btn-ms optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=chooseToUpdate";?>"><?php echo $formText_Cancel_input;?></a>
		</div>
		</div>
		<script type="text/javascript">
		<?php if(isset($ob_javascript)) { ob_start(); } ?>
		$(function() {
			$("a.delete_relation").unbind("click").on("click",function(e){
				e.preventDefault();
				if(!fw_changes_made && !fw_click_instance)
				{
					fw_click_instance = true;
					var $_this = $(this);
					bootbox.confirm({
						message:"<?php echo $formText_AreYouSureYouWantToDeleteRelation_input;?>: " + $_this.attr("data-name") + "?",
						buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
						callback: function(result){
							if(result)
							{
								window.location = $_this.attr("href");
							}
							fw_click_instance = false;
						}
					});
				}
			});
		});
		<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
		</script><?php

	} else if(!isset($_GET['editorder'])) {
		$relationValue = array('');
		if($_GET['relationedit'] != '-1')
		{
			include($module_absolute_path."/input/settings/relations/$submodule.php");
			$relationValue = explode("¤",$prerelations[$_GET['relationedit']]);
		}
		?>
		<form action="<?php echo $extradir."/input/includes/editRelations.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&relationedit=".$_GET['relationedit'];?>" method="post">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><?php echo $formText_Name_input;?></td>
			<td><input type="text" name="relation_name" value="<?php echo $relationValue[9];?>" /></td>
		</tr>
		<tr>
			<td><?php echo $formText_ItemsPerPage_input;?></td>
			<td><input type="text" name="items_per_page" value="<?php echo $relationValue[10];?>" /></td>
		</tr>
		<tr>
			<td><?php echo $formText_ExpandList_input;?></td>
			<td><input type="checkbox" name="expand_list" value="1"<?php echo ($relationValue[11]==1 ? 'checked':'');?> /></td>
		</tr>

		<tr>
			<td><?php echo $formText_InHiddenList_input;?></td>
			<td><input type="checkbox" name="in_hidden_list" value="1"<?php echo ($relationValue[12]==1 ? 'checked':'');?> /></td>
		</tr>
		<tr>
			<td><?php echo $formText_LinkToModuleId_input;?></td>
			<td><input type="checkbox" name="relation_link_to_module_id" value="1"<?php echo ($relationValue[13]==1 ? 'checked':'');?> /></td>
		</tr>
		<tr>
			<td><?=$formText_ChooseCurrentTableRelationField_input;?></td>
			<td><select onChange="javascript:sys_updateModuleField();" name="choosenparentfield" id="choosenparentfieldid">
			<?php
			if($relationValue[0] == '') $relationValue[0] = 'id';
			include($module_absolute_path."/input/settings/fields/".$submodule."fields.php");
			foreach($prefields as $pre)
			{
				$presplit = explode("¤",$pre);
				?><option value="<?=$presplit[0];?>" <?=($relationValue[0] == $presplit[0] ? 'selected="selected"':'');?>><?=$presplit[0];?></option><?php
			}
			?>
			</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $formText_ChooseChildTable_input;?></td>
			<td><select onChange="javascript:sys_updateModuleField();" name="choosenmoduletable" id="choosenmoduletableid">
			<option value="-1"><?php echo $formText_ChooseTable_input;?></option>
			<?php
			$findBase = opendir($account_absolute_path."/modules");
			while($writeBase = readdir($findBase))
			{
				$module_id = '';
				$o_query = $o_main->db->query('SELECT name FROM moduledata WHERE name = ?', array($writeBase));
				if($o_query && $o_row = $o_query->row()) $module_id = $o_row->name;
				$findTables = opendir($account_absolute_path."/modules/$writeBase/input/settings/tables");
				while($s_file = readdir($findTables))
				{
					if($s_file == '.' || $s_file == '..') continue;
					$fieldParts = explode(".",$s_file);
					if($fieldParts[2] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
					{
						if($firstTable == "")
						{
							$firstTable = $fieldParts[0];
						}
						?><option value="<?php echo $moduleID.":".$fieldParts[0].":".$writeBase;?>" <?php if($relationValue[1] == $module_id && $relationValue[2] == $fieldParts[0]){ ?>selected="selected"<?php } ?>><?php echo $writeBase." - ".$fieldParts[0];?></option><?php
					}
				}
			}
			?>
			</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $formText_ChooseChildField_input;?></td>
			<td><select  name="choosenmodulefield" id="choosenmodulefieldid">
			<option value="-1"><?php echo $formText_ChooseField_input;?></option>
			<?php
			include($account_absolute_path."/modules/$relationValue[1]/input/settings/fields/".$relationValue[2]."fields.php");
			foreach($prefields as $pre)
			{
				$presplit = explode("¤",$pre);
				?><option value="<?php echo $presplit[0];?>"<?php if($relationValue[3] == $presplit[0]){ ?>selected="selected"<?php } ?>><?php echo $presplit[0];?></option><?php
			}
			?>
			</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $formText_FilterByContentStatus_input;?></td>
			<td><input type="text" name="contentstatusfilter" value="<?php echo $relationValue[8];?>"></td>
		</tr>
		</table>
		<div class="content_buttons">
			<input class="btn btn-success btn-ms" type="submit" name="send" value="<?php echo $formText_save_input;?>" />
			<a class="btn btn-default btn-ms optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=editRelations";?>"><?php echo $formText_Cancel_input;?></a>
		</div>
	</form>

	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	function sys_updateModuleField()
	{
		var modulechoice = document.getElementById('choosenmoduletableid');
		var chosevalue = modulechoice.options[modulechoice.selectedIndex].value.split(":");
		$.ajax({
			url: "<?php echo $extradir;?>/input/includes/ajax_getModuleFields.php",
			data: {basemodule: chosevalue[1], firstmodule: '<?php echo $module;?>', modulebase: chosevalue[2]},
			cache: false,
			complete: function (data) {
				$('#choosenmodulefieldid').empty()
				if(data.responseText != "NONE")
				{
					var splitstring = data.responseText.split(";");
					$(splitstring).each(function(key, value)
					{
						subsplit = value.split(":");
						$('#choosenmodulefieldid').append($("<option></option>").attr("value", subsplit[0]).text(subsplit[1]));
					});
				}
			}
		});
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	<?php
	}
}
?>
