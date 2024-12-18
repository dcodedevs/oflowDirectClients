<?php
function get_form_text_variables($file)
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
	$pattern ='/\$formText_([a-zA-Z0-9_-])+/';
	//geting all values
	preg_match_all($pattern, $file_content, $matches);
	
	$pattern ='/\$formLongText_([a-zA-Z0-9_-])+/';
	preg_match_all($pattern, $file_content, $matches2);
	//print_r($matches2);
	$matches[0]=array_merge($matches[0],$matches2[0]);
	
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
?>