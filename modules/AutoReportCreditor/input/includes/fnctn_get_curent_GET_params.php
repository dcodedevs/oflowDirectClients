<?php
if(!function_exists('get_curent_GET_params')){
function get_curent_GET_params($exceptions = array())
{
	$get_keys = array_keys($_GET);
	$tmp_return='';
	foreach($get_keys as $key)
	{
		if (!in_array($key, $exceptions))
		{
			if (strlen($tmp_return)>0)
			{
				$tmp_return=$tmp_return.'&'.$key.'='.$_GET[$key];
			} else {
				$tmp_return=$key.'='.$_GET[$key];
			}
		}
	}
	return $tmp_return;
}
}