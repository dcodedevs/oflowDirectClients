<?php
if(!function_exists('get_form_text_variables')){
function get_form_text_variables($file)
{
	// function withc gets from files all url inform of  url(file.jpg);
	// necasary to see the linked files to css file
	$matches=array();
	// content into the string
	$file_content = file_get_contents($file);
	// patern for geting urls     
	$pattern ='/\$formText_([a-zA-Z0-9_-])+/';
	//geting all values
	preg_match_all($pattern, $file_content, $matches);
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