<?php
function mkdir_recursive($dir)
{
	if(!file_exists($dir))
	{
		$parent_dir = explode("/",$dir);
		array_pop($parent_dir);
		mkdir_recursive(implode("/",$parent_dir));
		mkdir($dir,0777);
	}
}
