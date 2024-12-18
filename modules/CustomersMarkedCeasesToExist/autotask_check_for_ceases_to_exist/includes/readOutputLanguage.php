<?php
// ************
// Read current module all output folder language variables.
// Version 2.0
// ************

if(!isset($o_main->language_variables)) $o_main->language_variables = array();

/*
** Include central language files
**/
if(isset($o_main->central_output_language) && $o_main->central_output_language)
{
	$v_items = array('empty', 'default', 'en');
	if('en' != $variables->languageID && 20 > $variables->developeraccess) $v_items[] = $variables->languageID;
	$l_count = 0;
	foreach($v_items as $s_lang)
	{
		$l_count++;
		if(is_file(BASEPATH.'languagesOutput/'.$s_lang.'.php')) include(BASEPATH.'languagesOutput/'.$s_lang.'.php');
		$o_main->language_variables = include_local(BASEPATH.'languagesOutput/'.$s_lang.'.php', $o_main->language_variables);
	}
	return;
}

$s_module_absolute_path = realpath(__DIR__.'/../../');												
if(!function_exists ("get_form_text_variables"))
{
	include_once($s_module_absolute_path.'/input/includes/fnctn_get_form_text_variables.php');
	include_once($s_module_absolute_path.'/input/includes/fnctn_get_files.php');
	include_once($s_module_absolute_path.'/input/includes/fnctn_get_language_variables.php');
}
$b_check_subfolders = 1;
$v_bt = debug_backtrace();
$v_caller = array_shift($v_bt);
$v_tmp = explode("/modules/", $v_caller['file']);
$v_tmp = explode("/", $v_tmp[1]);
$s_folder = $v_tmp[1];
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

$o_query = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();

$s_default_output_language = $v_row['languageID'];

$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/'.$s_default_output_language.'.php';
include($s_include);

foreach($v_variables_ids as $s_id) {
	if ( strlen(${$s_id}) == 0 || ${$s_id} == ' ' ) {
		${$s_id} = $v_variables[$s_id]['defaultValue']; 
	}	
}