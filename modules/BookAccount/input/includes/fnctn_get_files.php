<?php																																				
function get_files($dirs, $extensions, $except_dirs, $check_subdirs)
{
	//function for getting array of files from some given dirrectory
	//widely used this function in cssListingEditingModule
	
	$tmp_return = array();
	$sub_dirs = array();
	
	// goes trough list of directories
	foreach ($dirs as $dir) 
	{
		$scan = scandir($dir);
		foreach($scan as $file)
		{
			if(is_file($dir."/".$file))
			{
				// if its file then adds to the array of files
				// before checks if its in allowed list of extensions
				$file_info=pathinfo($dir.'/'.$file);
				if (in_array($file_info['extension'],$extensions))
				{
					array_push($tmp_return,$dir.'/'.$file);
				}
			}
			else
			{
				// if its not a file then its directory 
				// escaping  same directory and levelup directory
				if ($file!="." and $file!="..")
				{
					if (is_dir($dir."/".$file) and !in_array($dir."/".$file,$except_dirs))
					{
						array_push($sub_dirs,$dir.'/'.$file);
					}
				}
			}
		}
	}
	
	if (count($sub_dirs)>0 and $check_subdirs)
	{
		// if check_subdirs flag is set to 1
		// recursivery goes into subdirs
		$tmp_return=array_merge($tmp_return, get_files($sub_dirs,$extensions,$except_dirs, 1 )); 
	}
	return $tmp_return;
}
?>