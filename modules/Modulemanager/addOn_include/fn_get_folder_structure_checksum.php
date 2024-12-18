<?php
// ***
// Version 1.3
// Updated 2018-07-11
// 
// Get folder whole structure checksum
// Possible to force get file content checksum for whole folder structure
// ***
function get_folder_structure_checksum($s_path, $b_file_contet_checksum = FALSE, $b_sort = FALSE, $v_excludes = array(), &$s_checksum = '', $s_recursive_path = '', $l_level = 0, &$s_structure = '')
{
	if(is_file($s_path.$s_recursive_path))
	{
		return false;
	} else if(file_exists($s_path.$s_recursive_path) && !in_array($s_path.$s_recursive_path, $v_excludes)) {
		if(substr(sprintf('%o', fileperms($s_path.$s_recursive_path)), -1) <= 1)
		{
			$v_items = ftp_get_filelist(str_replace(realpath(__DIR__."/../../../"), "", $s_path.$s_recursive_path));
			foreach($v_items as $v_item) $v_scan[] = $v_item["name"];
		} else {
			$v_scan = scandir($s_path.$s_recursive_path);
		}
		foreach($v_scan as $s_item)
		{
			if($s_item == '.' || $s_item == '..') continue;
			if($b_sort) natsort($v_scan);
			if(is_file($s_path.$s_recursive_path.'/'.$s_item))
			{
				if(!in_array($s_path.$s_recursive_path.'/'.$s_item, $v_excludes))
				{
					$s_checksum .= $s_recursive_path.'/'.$s_item.':';
					$s_structure .= $s_recursive_path.'/'.$s_item.':';
					if($b_file_contet_checksum)
					{
						$s_checksum .= sha1_file($s_path.$s_recursive_path.'/'.$s_item).':';
						$s_structure .= sha1_file($s_path.$s_recursive_path.'/'.$s_item).PHP_EOL;
					}
				}
			} else {
				get_folder_structure_checksum($s_path, $b_file_contet_checksum, $b_sort, $v_excludes, $s_checksum, $s_recursive_path.'/'.$s_item, $l_level+1, $s_structure);
			}
		}
	} else {
		return false;
	}
	
	if($l_level == 0)
	{
		//file_put_contents(__DIR__.'/../checksum.txt', $s_structure.md5($s_checksum));//, FILE_APPEND);
		return md5($s_checksum);
	}
	return false;
}