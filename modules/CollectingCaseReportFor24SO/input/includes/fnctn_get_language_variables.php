<?php
if(!function_exists("devide_by_uppercase")) include_once(__DIR__."/fnctn_devide_by_upercase.php");
function get_language_variables($php_files)
{
	if(!is_array($php_files))
	{
		return;
	}
	$tmp_return=array();
	$variable_ids=array();
	foreach($php_files as $file)
	{
		//print ('<br />Checking the file:'.$file.'<br />');
		$formTextVariables=get_form_text_variables($file);
		if(count($formTextVariables))
		{
			foreach($formTextVariables as $formTextVar)
			{
				$formTextID=str_replace("$", "", $formTextVar);
				if(!in_array($formTextVar,$variable_ids))
				{
					$tmp_return[$formTextID]['files']=array();
					array_push($variable_ids,$formTextVar);
					
					$formTextName=explode("_", $formTextVar);
					//print_r($formTextName);
					$tmp_return[$formTextID]['context'] = (isset($formTextName[2]) ? devide_by_uppercase($formTextName[2]) : '');
					$tmp_return[$formTextID]['name']=$formTextName[1];
					$tmp_return[$formTextID]['defaultValue']=devide_by_uppercase($formTextName[1]);
					array_push($tmp_return[$formTextID]['files'], $file);
				} else {
					array_push($tmp_return[$formTextID]['files'], $file);
				}
			}
		}
	}
	return $tmp_return;
}
?>