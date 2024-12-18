<?php
/*
** Version 8.02
** Created: 2015-10-15
** Updated: 2016-09-06
*/
 
function get_folder_version($s_path)
{
	$s_version = "";
	if($o_handle = opendir($s_path))
	{
		while(($s_file = readdir($o_handle)) !== false )
		{
			if(substr($s_file, -4) == ".ver" && !stristr($s_file,".LCK"))
			{
				$s_version = str_replace("_",".",substr($s_file,0,-4));
				break;
			}
		}
	} 
	closedir($o_handle);
	return $s_version;
}
?>