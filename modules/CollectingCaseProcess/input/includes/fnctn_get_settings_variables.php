<?php
include_once(__DIR__."/fnctn_devide_by_upercase.php");
function get_settings_variables($php_files)
{
	if(!is_array($php_files))
	{
		return;
	}
	$tmp_return=array();
	$variable_ids=array();
	foreach($php_files as $file)
	{
		$formTextVariables=get_settings_variables_from_file($file);
		if(count($formTextVariables))
		{
			foreach($formTextVariables as $formTextVar)
			{
				$formTextID=str_replace("$", "", $formTextVar);
				$tmp_return[$formTextID]['files']=array();
				if(!in_array($formTextVar,$variable_ids))
				{
					array_push($variable_ids,$formTextVar);
					
					$formTextName=explode("_", $formTextVar);
					if (substr_count($formTextVar, "settingsChoice_")>0)
					{
						$tmp_return[$formTextID]['type']='Choice';
					} else {
						if (substr_count($formTextVar, "settingsOnOff_")>0)
						{
							$tmp_return[$formTextID]['type']='OnOff';
						} else {
							$tmp_return[$formTextID]['type']='Var';
						}
					}
					
					$tmp_return[$formTextID]['context']=devide_by_uppercase($formTextName[2]);
					$tmp_return[$formTextID]['name']=$formTextName[1];
					$tmp_return[$formTextID]['access']='Super';
					$tmp_return[$formTextID]['choices']=array();
					$tmp_return[$formTextID]['editPossible']='Yes';
					
					//$tmp_return[$formTextID]['defaultValue']=devide_by_uppercase($formTextName[1]);
					array_push($tmp_return[$formTextID]['files'], $file);
				} else {
					array_push($tmp_return[$formTextID]['files'], $file);
				}
			}
		}
	}
	return $tmp_return;
}

function get_settings_variables_from_file($file)
{
	// function withc gets from files all ursl 
    // inform of  url(file.jpg);
    //
    // necasary to see the linked files to css file
    $matches=array();
    
    $matches2=array();
    
    $matches3=array();
    $tmp_return=array();
    // content into the string
    $file_content = file_get_contents($file);
    // patern for geting urls     
    $pattern ='/\$settingsVar_([a-zA-Z0-9_-])+/';
    //geting all values
    preg_match_all($pattern, $file_content, $matches);
    // retunrs first element couse in second element there is places in the text file
    
    $pattern ='/\$settingsChoice_([a-zA-Z0-9_-])+/';
    preg_match_all($pattern, $file_content, $matches2);
    
    $pattern ='/\$settingsOnOff_([a-zA-Z0-9_-])+/';
    preg_match_all($pattern, $file_content, $matches3);
    
    $matches[0] = array_merge($matches[0], $matches2[0],$matches3[0]);
	
	foreach ($matches[0] as $match)
	{
		if(substr_count($match," "))
		{
			$match=explode(" ", $match);
			$match=$match[0];
		}
		if(substr_count($match,";"))
		{
			$match=explode(";", $match);
			$match=$match[0];
		}
		if(substr_count($match,'"'))
		{
			$match=explode('"', $match);
			$match=$match[0];
		}
		if(substr_count($match,"."))
		{
			$match=explode(".", $match);
			$match=$match[0];
		}
		if(substr_count($match,"="))
		{
			$match=explode("=", $match);
			$match=$match[0];
		}
		if(substr_count($match,":"))
		{
			$match=explode(":", $match);
			$match=$match[0];
		}
		if(!in_array($match,$tmp_return)) 
		{
			array_push($tmp_return,$match);
		}
	}
	return $tmp_return;
}
?>