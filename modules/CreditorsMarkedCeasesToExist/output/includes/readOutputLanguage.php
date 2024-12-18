<?php
// ************
// Read current module all output folder language variables.
// Version 1.8
// ************

if(!isset($o_main->language_variables)) $o_main->language_variables = array();
if(!function_exists("include_local")) include(__DIR__."/../../input/includes/fn_include_local.php");

/*
** Include central language files (10.003)
**/
if($o_main->central_output_language)
{
	$s_object = $module;
	if(empty($s_object)) { $v_tmp = explode('/', realpath(__DIR__.'/../../')); $s_object = array_pop($v_tmp); }
	$o_main->language_loaded[$s_object] = TRUE;
	$v_items = array('default');
	if(20 > $variables->developeraccess) $v_items[] = $variables->languageID;
	foreach($v_items as $s_lang)
	{
		$include_file = BASEPATH.'languagesOutput/'.$s_lang.'_'.$s_object.'.php';
		if(is_file($include_file))
		{
			include($include_file);
			$o_main->language_variables = include_local($include_file, $o_main->language_variables);
		}
	}
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_local_languagevariable WHERE".($o_main->multi_acc?" account_id = '".$o_main->db->escape_str($o_main->account_id)."' AND":"")." module_name = '".$o_main->db->escape_str($module)."'");
	$local_languagevariables = $o_query ? $o_query->result_array() : array();
	if(count($local_languagevariables) > 0) {
		foreach($local_languagevariables as $local_languagevariable) {
			${$local_languagevariable['languagevariable_name']} = $local_languagevariable['text'];
			$o_main->language_variables[$local_languagevariable['languagevariable_name']] = $local_languagevariable['text'];
		}
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
/*$v_addon_folders = array();
$v_scan = scandir($s_module_absolute_path);
foreach($v_scan as $s_file)
{
	if(strtolower(substr($s_file,0,6)) == 'addon_')
		$v_addon_folders[] = $s_file;
}*/
$b_check_subfolders = 1;
$s_folder = (isset($_POST['folder']) ? $_POST['folder'] : $_GET['folder']);
if($s_folder == "")
{
	$s_folder = explode("/", realpath(__DIR__."/../"));
	$s_folder = $s_folder[sizeof($s_folder)-1];
}
$v_folders = array($s_module_absolute_path.'/'.$s_folder.'/');
$v_extensions = array('php');
$v_except_folders = array();//$s_module_absolute_path.'/input');
//foreach($v_addon_folders as $s_addon_folder) $v_except_folders[] = $s_module_absolute_path.'/'.$s_addon_folder;

$v_files = get_files($v_folders, $v_extensions, $v_except_folders, $b_check_subfolders);
$v_variables = get_language_variables($v_files);
$v_variables_ids = array_keys($v_variables);

$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/empty.php';
if(is_file($s_include)) include($s_include);

foreach($v_variables_ids as $s_id)
{
	${$s_id} = $v_variables[$s_id]['defaultValue']; 
}
$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/default.php';
if(is_file($s_include)) include($s_include);

$b_show_choosen_language = true;
if(isset($variables))
{
	if(isset($variables->developeraccess) && $variables->developeraccess == 20) $b_show_choosen_language = false;
} else {
	$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
	$o_query = $o_main->db->query($s_sql);
	$fw_session = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
	if($fw_session['developeraccess'] == 20) $b_show_choosen_language = false;
}

if($b_show_choosen_language)
{
	$s_default_output_language = '';
	$o_lang_query = $o_main->db->query("SELECT languageID FROM language WHERE languageID = '".$o_main->db->escape_str($variables->languageID)."' AND outputlanguage = 1");
	if($o_lang_query && $o_lang_query->num_rows()>0)
	{
		$o_row = $o_lang_query->row();
		$s_default_output_language = $o_row->languageID;
	} else {
		$o_lang_query = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
		if($o_lang_query && $o_row = $o_lang_query->row())
		$s_default_output_language = $o_row->languageID;
	}
	
	$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/'.$s_default_output_language.'.php';
	//echo $s_include;
	if(is_file($s_include))
	{
		include($s_include);
		foreach($v_variables_ids as $s_id)
		{
			if ( strlen(trim(${$s_id})) == 0 )
			{
				${$s_id} = $v_variables[$s_id]['defaultValue'];
			}
		}
	}
	
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_local_languagevariable WHERE".($o_main->multi_acc?" account_id = '".$o_main->db->escape_str($o_main->account_id)."' AND":"")." module_name = '".$o_main->db->escape_str($module)."'");
	$local_languagevariables = $o_query ? $o_query->result_array() : array();
	if(count($local_languagevariables) > 0) {
		foreach($local_languagevariables as $local_languagevariable) {
			${$local_languagevariable['languagevariable_name']} = $local_languagevariable['text'];
		}
	}
}

foreach($v_variables_ids as $var_id)
{
	$o_main->language_variables[$var_id] = ${$var_id}; 
}

// Addon to translate language directly
if(5 == $variables->developeraccess && !isset($_POST['skip_translate']))
{
	$s_module_name = substr(strrchr($s_module_absolute_path, "/"), 1);
	foreach($v_variables_ids as $s_id)
	{
		if (FALSE === strpos(${$s_id}, 'sys_translate_'.$s_id.'@'.$s_folder.'@'.$s_module_name.'@'))
		{
			${$s_id} .= ' sys_translate_'.$s_id.'@'.$s_folder.'@'.$s_module_name.'@';
		}
	}
}