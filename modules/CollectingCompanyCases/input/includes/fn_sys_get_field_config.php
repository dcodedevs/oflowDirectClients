<?php
if(!function_exists('sys_get_field_config')){
function sys_get_field_config($file, $account_absolute_path, $databases)
{
	$return = array();

	$settingsFile = file($account_absolute_path.$file);
	$useLines = "";
	foreach($settingsFile as $testLine)
	{
		$use = trim($testLine);
		if($use[0] == "$")
		{
			$useLines = $use;
		}
	}

	$splitOne = explode("array(",$useLines);
	$variabelBase = str_replace(");","",$splitOne[1]);

	$variabSplit = explode("\",\"",$variabelBase);

	foreach($variabSplit as $ab)
	{
		$ut = str_replace("\"","",$ab);
		$ubSplit = explode("¤",$ut);
		$tempvariab = new Field();
		$tempvariab->init($ubSplit[0],$ubSplit[1],$ubSplit[2],$ubSplit[3],$ubSplit[4],$ubSplit[5],$ubSplit[6],$ubSplit[9],$ubSplit[10],$ubSplit[11],$ubSplit[12],$ubSplit[13],$ubSplit[14],$ubSplit[15],$ubSplit[16],$ubSplit[17]);
		$tempvariab->multilanguage = $databases[$tempvariab->database]->multilanguage;
		$return[$ubSplit[1]] = $tempvariab;
	}

	return $return;
}
}