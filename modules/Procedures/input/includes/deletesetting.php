<?php
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
require_once(__DIR__."/ftp_commands.php");

$module = $_GET['module'];
$submodule = $_GET['submodule'];

if(isset($_GET['deleteField']))
{
	require_once(__DIR__."/class_Field.php");
	require_once(__DIR__."/fn_sys_get_field_config.php");
	
	include(__DIR__."/fieldloader.php");
	$file = "/modules/$module/input/settings/fields/".$submodule."fields.php";
	$variables = sys_get_field_config($file, $account_absolute_path, $databases);
	
	$deleteFieldName = $_GET['deleteField'];
	$deleteField = $variables[$deleteFieldName];
	
	if(is_file($module_absolute_path."/input/fieldtypes/field".$deleteField->type."/deleteField.php"))
	{
		include($module_absolute_path."/input/fieldtypes/field".$deleteField->type."/deleteField.php");
	}
	
	$makeString = "";
	foreach($variables as $nut)
	{
		
		if($nut->formname != $deleteFieldName)
		{
			if($makeString != "")
			{
				$makeString .= "\",\"";
			}
			if(strpos($nut->htmlname,'$') !== false and strpos($nut->htmlname,'{') === false) $nut->htmlname = '{'.$nut->htmlname.'}';
			$makeString .= $nut->sqlname."¤".$nut->formname."¤".$nut->htmlname."¤".$nut->database."¤".$nut->type."¤".$nut->extra."¤".$nut->default."¤¤¤".$nut->hidden."¤".$nut->readonly."¤".$nut->extravalue."¤".$nut->update."¤".$nut->insert."¤".$nut->mandatory."¤".$nut->duplicate."¤".$nut->fieldwidth."¤".$nut->paddingTop."¤".$nut->paddingWidth."¤";
		}
	}
	unset($variables[$deleteFieldName]);
	
	$outString = "\$prefields = array(\"$makeString\");";
	ftp_file_put_content("/modules/$module/input/settings/fields/".$submodule."fields.php","<?php\n{$outString}\n?>");
}


if(isset($_GET['deleteButtonField']))
{
	require_once(__DIR__."/class_Button.php");
	require_once(__DIR__."/fn_sys_get_button_config.php");
	
	$deleteFieldName = $_GET['deleteButtonField'];
	$variables = sys_get_button_config("/modules/$module/input/settings/buttonconfig/".$submodule.$_GET['buttontype'].".php", $account_absolute_path);
	
	$makeString = "";
	foreach($variables as $key => $nut)
	{
		if($key != $deleteFieldName)
		{
			if(strpos($nut->buttonname,'$') !== false and strpos($nut->buttonname,'{') === false) $nut->buttonname = '{'.$nut->buttonname.'}';
			$makeString .= $nut->buttonnamelist.":".$nut->buttonname.":".$nut->selectedbutton.":".$nut->tableconnect.":".$nut->hiddenfield.":".$nut->hiddendirectfield.":".$nut->tableconnectmodule."¤";
		}
	}
	
	$outString = "\$prebuttonconfig = \"$makeString\";";
	ftp_file_put_content("/modules/$module/input/settings/buttonconfig/".$submodule.$_GET['buttontype'].".php","<?php\n{$outString}\n?>");
}



if(isset($_GET['deleteTable']))
{
	$deleteTable = $_GET['deleteTable'];
	if(is_file($module_absolute_path."/input/settings/tables/$deleteTable.php"))
	{
		include($module_absolute_path."/input/settings/tables/$deleteTable.php");
		if(sizeof($prechildmodule) == 0)
		{
			$parentFrom = $preparentmodule;
			$includefile = $module_absolute_path."/input/settings/tables/".$parentFrom.".php";
			if(is_file($includefile))
			{
				$settingsFile = file($includefile);
				$anLines = array();
				foreach($settingsFile as $testLine)
				{
					$use = trim($testLine);
					if($use[0] == "$")
					{
						$anLines[] = $use;
					}
				}
				$workLine = explode("array(",$anLines[1]);
				$moduleString = trim(str_replace(");","",$workLine[1]));
				$moduleString = str_replace("\"","",$moduleString);
				$childWork = explode(",",$moduleString);
				$newChildString = "";
				foreach($childWork as $foradd)
				{
					if($foradd != $deleteTable)
					{
						if($newChildString != "")
						{
							$newChildString .= ",";
						}
						$newChildString .= "\"$foradd\"";
					}
				}
				$anLines[1] = "\$prechildmodule = array(".$newChildString.");";
				$newFile = "";
				for($l = 0; $l<sizeof($anLines); $l++)
				{
					$newFile .= $anLines[$l].PHP_EOL;
				}
				ftp_file_put_content("modules/$module/input/settings/tables/".$parentFrom.".php","<?php\n{$newFile}\n?>");	
			}
			
			ftp_delete_file("modules/$module/input/settings/tables/$deleteTable.php");
			ftp_delete_file("modules/$module/input/settings/fields/".$deleteTable."fields.php");
			//DROP TABLE ".$deleteTable
			//DROP TABLE ".$deleteTable."content
		}
	}
}


header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
exit;
?>