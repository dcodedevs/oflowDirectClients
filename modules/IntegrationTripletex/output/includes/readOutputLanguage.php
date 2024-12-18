<?php
// ************
// Read current module all output folder language variables.
// Version 1.4
// ************
$s_module_absolute_path = realpath(__DIR__.'/../../');												
if(!function_exists ("get_form_text_variables"))
{
	include_once($s_module_absolute_path.'/input/includes/fnctn_get_form_text_variables.php');
	include_once($s_module_absolute_path.'/input/includes/fnctn_get_files.php');
	include_once($s_module_absolute_path.'/input/includes/fnctn_get_language_variables.php');
}
/*$v_addon_folders = array();
$v_scan = scandir($s_module_absolute_path);
foreach($v_scan as $s_file)
{
	if(strtolower(substr($s_file,0,6)) == 'addon_')
		$v_addon_folders[] = $s_file;
}*/

$b_check_subfolders = 1;
$s_folder = (isset($_POST['folder']) ? $_POST['folder'] : $_GET['folder']);
$v_folders = array($s_module_absolute_path.'/'.$s_folder.'/');
$v_extensions = array('php');
$v_except_folders = array();//$s_module_absolute_path.'/input');
//foreach($v_addon_folders as $s_addon_folder) $v_except_folders[] = $s_module_absolute_path.'/'.$s_addon_folder;

$v_files = get_files($v_folders, $v_extensions, $v_except_folders, $b_check_subfolders);
$v_variables = get_language_variables($v_files);
$v_variables_ids = array_keys($v_variables);

$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/empty.php';
include($s_include);

foreach($v_variables_ids as $s_id)
{
	${$s_id} = $v_variables[$s_id]['defaultValue']; 
}
$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/default.php';
include($s_include);

$b_show_choosen_language = true;
if(isset($variables))
{
	if(isset($variables->developeraccess) && $variables->developeraccess == 20) $b_show_choosen_language = false;
} else {
	$fw_session = mysql_fetch_assoc(mysql_query("select * from session_framework where companyaccessID = '".mysql_real_escape_string($_GET['caID'])."' and session = '".$_COOKIE['sessionID']."' and username = '".$_COOKIE['username']."'"));
	
	if($fw_session['developeraccess'] == 20) $b_show_choosen_language = false;
}

if($b_show_choosen_language)
{
	$v_row = mysql_fetch_assoc(mysql_query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC"));
	$s_default_output_language = $v_row['languageID'];
	
	$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/'.$s_default_output_language.'.php';
	include($s_include);
	
	foreach($v_variables_ids as $s_id) {
		if ( strlen(${$s_id}) == 0 || ${$s_id} == ' ' ) {
			${$s_id} = $v_variables[$s_id]['defaultValue']; 
		}	
	}
}
?>