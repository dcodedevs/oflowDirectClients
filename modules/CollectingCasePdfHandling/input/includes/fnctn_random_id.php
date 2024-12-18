<?php
function random_id($lenght)
{
	//random id function for getting images updated at the upload
	//to get around browser cashing, so that some pictures would be always refreshed
	//will be lower performance but picture always upto date
	//use <img src="file.jpg?'.random_id(10).'";
	$symbols = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','1','2','3');
	$tmp_return="";
	for ($i=0;$i<$lenght;$i++)
	{
		$tmp_return = $tmp_return. $symbols[rand(0, (count($symbols)-1))];
	}
	return $tmp_return;
}
?>