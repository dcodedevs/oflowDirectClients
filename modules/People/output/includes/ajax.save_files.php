<?php
$protected = false;
define('__DIR__', dirname(__DIR__));
$account_path = __DIR__.'/../../../..';
if(!function_exists("dirsizeexec")) include(__DIR__."/../../input/fieldtypes/File/fn_dirsizeexec.php");
if(!function_exists("mkdir_recursive")) include(__DIR__."/../../input/fieldtypes/File/fn_mkdir_recursive.php");

// // Add new
// if ($_POST['fileuploadaction'] == 'add') {
// 	$files = array();
// 	$query = "SELECT * FROM uploads WHERE content_module_id = '$moduleID' AND content_table = 'cases' AND content_field = 'files' AND handle_status = 0";
// 	$result = mysql_query($query);
// 	while($item = mysql_fetch_array($result)) {
// 		$id = $item['id'];
// 		$uploads_dir = ($protected ? 'protected/' : 'uploads/');
// 		$file_path = $uploads_dir.$item['id']."/".$item['filename'];
// 		mkdir_recursive(dirname($account_path."/".$file_path));
// 		copy($account_path."/".rawurldecode($item['filepath']), $account_path."/".$file_path);
// 		$file = array(
// 			'0' => $item['filename'],
// 			'1' => array($file_path),
// 			'2' => array(),
// 			'3' => '',
// 			'4' => $id
// 		);
// 		mysql_query("UPDATE uploads SET handle_status = '1' WHERE id = '$id'");
// 		$files[] = $file;
// 	}
//
// 	// Get old files
// 	$query = "SELECT * FROM cases WHERE id = '".sql_esc($_POST['cid'])."'";
// 	$result = mysql_query($query);
// 	if(mysql_num_rows($result)) {
// 		$row = mysql_fetch_array($result);
// 		$oldfiles = json_decode($row['files']);
// 		if(is_array($oldfiles)) $files = array_merge($oldfiles,$files);
// 	}
//
// 	// Save files to content field
// 	$files_json = json_encode($files);
// 	$sql = "UPDATE cases SET files = '$files_json' WHERE id = '".sql_esc($_POST['cid'])."'";
// 	mysql_query($sql);
// }

// Delete
if (isset($_POST['fileuploadaction']) && $_POST['fileuploadaction'] == 'delete') {
	if(isset($_POST['deletefileid']) && $_POST['deletefileid'] > 0) {
		$sql = "UPDATE sys_filearchive_file SET content_status = '2' WHERE id = ?";
		$o_main->db->query($sql, array($_POST['deletefileid']));
	}
}

?>
