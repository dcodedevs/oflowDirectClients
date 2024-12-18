<?php
// ************
// Independent CronJob script which compare two folders on server and store result in DB
// Version 1.1
// ************
define("BASEPATH", realpath(__DIR__."/../../../").DIRECTORY_SEPARATOR);
require_once(BASEPATH."elementsGlobal/cMain.php");

$_POST['folder'] = 'output_cronjob';
include(__DIR__."/../output/includes/readOutputLanguage.php");

$l_status = 0;
$v_parameters = $_SERVER['argv'];
$l_cronjob_id = intval($v_parameters[1]);

$o_query = $o_main->db->query("SELECT * FROM sys_cronjob WHERE id = ?", array($l_cronjob_id));
if($o_query && $o_query->num_rows()>0)
{
	$v_cronjob = $o_query->row_array();
	$v_variables = json_decode($v_cronjob['parameters'], true);
	foreach($v_variables as $s_key => $s_value) ${$s_key} = $s_value;
	
	$o_query = $o_main->db->get_where('sys_compare_folder', array('id'=>$l_sys_compare_folder_id, 'status'=>0));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_sys_compare_folder = $o_query->row_array();
		$v_item = json_decode($v_sys_compare_folder['compare_path_left'], TRUE);
		$s_compare_path_left = $v_item['path'];
		$v_object_left = array();
		$v_object_left = scanAllDirectory($s_compare_path_left, $s_compare_path_left, $v_object_left, $v_item['skip']);
		$v_object_left = sortFiles($v_object_left, $s_compare_path_left);
		
		$v_item = json_decode($v_sys_compare_folder['compare_path_right'], TRUE);
		$s_compare_path_right = $v_item['path'];
		$v_difference = array();
		$v_object_right = array();
		$v_object_right = scanAllDirectory($s_compare_path_right, $s_compare_path_right, $v_object_right, $v_item['skip']);
		$v_object_right = sortFiles($v_object_right, $s_compare_path_right);
		
		foreach($v_object_left as $newPath => $newFiles){			
			if(isset($v_object_right[$newPath])){
				$oldFiles = $v_object_right[$newPath];
				foreach($newFiles as $file){
					$addedFile = true;
					$changedFile = false;
					foreach($oldFiles as $oldFile){
						if($file['filename'] == $oldFile['filename']){
							if($file['hash'] != $oldFile['hash']){
								$changedFile = true;
							}
							$addedFile = false;
							break;
						}
					}
					if($addedFile){
						$v_difference[$file['path']."/".$file['filename']] = "A";
					}
					if($changedFile){
						$v_difference[$file['path']."/".$file['filename']] = "C";
					}
				}
			}else{
				foreach($newFiles as $file){
					$v_difference[$file['path']."/".$file['filename']] = "A";
				}
			}
		}
		foreach($v_object_right as $oldPath => $oldFiles){			
			if(isset($v_object_left[$oldPath])){
				$newsFiles = $v_object_left[$oldPath];
				foreach($oldFiles as $file){
					$deteledFile = true;
					foreach($newsFiles as $newFile){
						if($file['filename'] == $newFile['filename']){
							$deteledFile = false;
							break;
						}
					}
					if($deteledFile){
						$v_difference[$file['path']."/".$file['filename']] = "D";	
					}
				}
			}else{
				foreach($oldFiles as $file){
					$v_difference[$file['path']."/".$file['filename']] = "D";	
				}
			}
		}
		
		$o_query = $o_main->db->query("UPDATE sys_compare_folder SET difference = ?, status = 1 WHERE id = ?", array(json_encode($v_difference), $l_sys_compare_folder_id));
	}
	
	echo "Script finished\n";
} else {
	echo "Error: sys_cronjob not found by ID: ".$l_cronjob_id."\n";
}

//get all files from directory and subdirectories
function scanAllDirectory($s_path, $s_path_remove, $v_files, $v_skip)
{
	$v_exclude = array('..', '.');
	$v_items = array_diff(scandir($s_path), $v_exclude);
	foreach($v_items as $s_file){
		if(in_array($s_path."/".$s_file, $v_skip)) continue;
		
		if(is_dir($s_path."/".$s_file)){
			$v_files = scanAllDirectory($s_path."/".$s_file, $s_path_remove, $v_files);
		}else{
			$s_path_real = str_replace($s_path_remove, "", $s_path);
			array_push($v_files, array($s_path_real => array("filename"=>$s_file, "hash"=>sha1_file($s_path."/".$s_file), "path"=>$s_path_real)));
		}
	}
	return $v_files;
}

//sort files array, grouping by folder and changing array keys
function sortFiles($v_files, $s_root_path){
	$v_return = array();
	foreach($v_files as $s_key => $v_file){
		foreach($v_file as $s_path => $v_file_info){			
			$s_path = str_replace($s_root_path, "", $s_path);
			if(isset($v_return[$s_path])){
				$v_item = $v_return[$s_path];
			}else{
				$v_item = array();
			}
			array_push($v_item, $v_file_info);
			$v_return[$s_path] = $v_item;
		}
	}
	return $v_return;
}
?>