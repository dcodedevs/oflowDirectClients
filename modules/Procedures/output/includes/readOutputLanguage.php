<?php												
if (!function_exists ("get_form_text_variables"))
{
	include_once ($extradir.'/input/includes/fnctn_get_form_text_variables.php');
	include_once ($extradir."/input/includes/fnctn_get_files.php");
	include_once ($extradir."/input/includes/fnctn_get_language_variables.php");
}
$addonFolders = array();
$scan = scandir($extradir);
foreach($scan as $file)
{
	if(strtolower(substr($file,0,6)) == 'addon_')
		$addonFolders[] = $file;
}

$dirs=array($extradir);
$extensions=array('php');
$except_dirs=array($extradir."/input");
foreach($addonFolders as $addonFolder) $except_dirs[] = $extradir."/".$addonFolder;
$check_subdirs=1;
$files=get_files($dirs, $extensions,$except_dirs,$check_subdirs);
$data_of_variables=get_language_variables($files);
$variable_ids=array_keys($data_of_variables);
$folder = (isset($_POST['folder']) ? $_POST['folder'] : $_GET['folder']);

$dir=$extradir."/".$folder."/languagesOutput/empty.php";
//print($dir."<br />");
include($dir);

foreach($variable_ids as $var_id)
{
	//print('here  <br/>'.$var_id.'-->'.$data_of_variables[$var_id]['defaultValue'].'<br/>');
	${$var_id}=$data_of_variables[$var_id]['defaultValue']; 
}
$dir=$extradir."/".$folder."/languagesOutput/default.php";
//print($dir."<br />");
include($dir);

$dir=$extradir."/".$folder."/languagesOutput/$choosenListInputLang.php";
//print($dir."<br />");
include($dir);
?>