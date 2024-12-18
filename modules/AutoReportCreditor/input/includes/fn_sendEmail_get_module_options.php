<?php
if(!function_exists('sendEmail_get_module_options')){
function sendEmail_get_module_options($data, $table, $languageID)
{
	$fields = explode(',',$data[3]);
	$options = array();
	$dir = __DIR__."/../../../".$data[2];
	include($dir."/input/languagesInput/empty.php");
	include($dir."/input/languagesInput/default.php");
	include($dir."/input/languagesInput/$languageID.php");
	include($dir."/input/settings/fields/".$table."fields.php");
	
	foreach($prefields as $child)
	{
		$addToPre = explode("¤",$child);
		/*$tempre = $addToPre[6];
		$addToPre[6] = array();
		$addToPre[6]['all'] = $tempre;
		$fieldsStructure[$addToPre[0]] = $addToPre;*/
		if(in_array($addToPre[0], $fields))
		{
			if($addToPre[4] == 'Checkbox' or $addToPre[4] == 'GroupCheckboxField')
			{
				$options[] = array($addToPre[2], 1, $addToPre[0]);
				$options[] = array('(NOT) '.$addToPre[2], 0, $addToPre[0]);
			}
			if($addToPre[4] == 'Dropdown' or $addToPre[4] == 'GroupDropdownField')
			{
				$x = explode("::",$addToPre[11]);
				foreach($x as $item)
				{
					$item = explode(":",$item);
					$options[] = array($item[1], $item[0], $addToPre[0]);
				}
			}
		}
	}
	return $options;
}
}