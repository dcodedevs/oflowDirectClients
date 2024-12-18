<?php
$dir = realpath(__DIR__."/../../");
$inputversion = '';
if(is_dir($dir)) 
{
	if($dh = opendir($dir))
	{
		while(($file = readdir($dh)) !== false )
		{
			if(strpos($file,".ver") !== false)
			{
				$inputversion = str_replace("_",".",substr($file,0,strpos($file,".ver")));
			}
		}
		closedir($dh);
	}
}
print $module ."|". $inputversion;
?>