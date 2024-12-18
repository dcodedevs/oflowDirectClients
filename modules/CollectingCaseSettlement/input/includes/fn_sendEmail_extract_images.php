<?php
function sendEmail_extract_images($str)
{
	$return = array();
	// read all image tags into an array
	preg_match_all('/<img[^>]+>/i',$str, $imgTags); 
	
	for ($i = 0; $i < count($imgTags[0]); $i++)
	{
		// get the source string
		preg_match('/src="([^"]+)/i',$imgTags[0][$i], $image);
		
		// remove opening 'src=' tag, can`t get the regex right
		$return[] = str_ireplace( 'src="', '',  $image[0]);
	}
	
	$imgTags = array();
	// read all background images into an array
	preg_match_all('/background-image:[^;]+;/i',$str, $imgTags); 
	
	for ($i = 0; $i < count($imgTags[0]); $i++)
	{
		// get the source string
		preg_match('/url\(([^\)]+)/i',$imgTags[0][$i], $image);
		// remove opening 'src=' tag, can`t get the regex right
		$return[] = str_ireplace( 'url(', '',  $image[0]);
	}
	
	// will output all your img src's within the html string
	return $return;
}