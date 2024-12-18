<?php
if(!function_exists('get_accesselement_variables')){
function get_accesselement_variables($output_dir, $type = "allow") {

    //define extension of the files
    $extensions=array('php');
	//should check subdirs
    $check_subdirs=1;
    //gets files
	$tmp_return=array();
    $accessTypeString = "accessElementAllow";
    if($type == "restrict"){
        $accessTypeString = "accessElementRestrict";
    }
    $except_dirs=array(realpath($output_dir."/languagesOutput"));
    $directory_id = basename($output_dir);

    $output_files = get_files(array($output_dir), $extensions,$except_dirs,$check_subdirs);
    if(count($output_files) > 0) {
        $variable_ids=array();
        foreach($output_files as $file)
        {
            $formTextVariables=get_access_element_variables($file, $accessTypeString);
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
    }


    return $tmp_return;
}
}
if(!function_exists('get_access_element_variables')){
function get_access_element_variables($file, $string)
{
	// function withc gets from files all ursl
	// inform of  url(file.jpg);
	//
	// necasary to see the linked files to css file
	$matches=array();
	$matches2=array();
	// content into the string
	$file_content = file_get_contents($file);
	// patern for geting urls
	$pattern ='/\$'.$string.'_([a-zA-Z0-9_-])+/';
	//geting all values
	preg_match_all($pattern, $file_content, $matches);

	// $pattern ='/\$formLongText_([a-zA-Z0-9_-])+/';
	// preg_match_all($pattern, $file_content, $matches2);
	// //print_r($matches2);
	// $matches[0]=array_merge($matches[0],$matches2[0]);

	// retunrs first element couse in second element there is places in the text file
	$tmp_return=array();
	foreach($matches[0] as $match)
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
}