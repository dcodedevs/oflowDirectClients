<?php
if(!function_exists('sendEmail_extract_attachments')){
function sendEmail_extract_attachments($str)
{
	$return = array();
	// read all image tags into an array
	preg_match_all('/<div[^>]+>/i',$str, $imgTags); 
	
	for ($i = 0; $i < count($imgTags[0]); $i++)
	{
		// get the source string
		preg_match('/data-attachment="([^"]+)/i',$imgTags[0][$i], $image);
		
		// remove opening 'src=' tag, can`t get the regex right
		$return[] = str_ireplace( 'data-attachment="', '',  $image[0]);
	}
	
	// will output all your img src's within the html string
	return $return;
}
}