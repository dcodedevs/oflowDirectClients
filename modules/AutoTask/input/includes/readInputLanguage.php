<?php
$l_empty_input_language_variables = 0;
$v_language_variables = array();
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
if(!function_exists("ftp_file_put_content"))
{
	include_once($account_absolute_path."/ftpConnect.php");
	include_once(__DIR__."/ftp_commands.php");
}
if(!function_exists("get_form_text_variables"))
{
	include_once(__DIR__."/fnctn_get_form_text_variables.php");
	include_once(__DIR__."/fnctn_get_files.php");
	include_once(__DIR__."/fnctn_get_language_variables.php");
}
if(!isset($choosenListInputLang))
{
	if(isset($_POST['choosenListInputLang'])) $choosenListInputLang = $_POST['choosenListInputLang'];
	else if(isset($_GET['choosenListInputLang'])) $choosenListInputLang = $_GET['choosenListInputLang'];
}
$show_choosen_language = true;
if(isset($variables))
{
	if(isset($variables->developeraccess) && $variables->developeraccess == 20) $show_choosen_language = false;
} else {
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	
	if($fw_session['developeraccess'] == 20) $show_choosen_language = false;
}
$addonFolders = array();
$scan = scandir($module_absolute_path);
foreach($scan as $file)
{
	if(strtolower(substr($file,0,6)) == 'addon_')
		$addonFolders[] = $file;
}

$check_subdirs=1;
$extensions=array('php');
$dirs=array($module_absolute_path."/input");
$except_dirs=array($module_absolute_path."/input/languagesInput");
foreach($addonFolders as $addonFolder)
{
	$dirs[] = $module_absolute_path."/$addonFolder";
	$except_dirs[] = $module_absolute_path."/$addonFolder/languagesInput";
}
foreach($dirs as $key => $dir)
{
	$files=get_files(array($dir), $extensions, $except_dirs, $check_subdirs);
	$data_of_variables=get_language_variables($files);
	$variable_ids=array_keys($data_of_variables);
	if(!is_file($dir."/languagesInput/empty.php") or !is_file($dir."/languagesInput/default.php"))
	{
		$defaultPhpData="<"."?php".PHP_EOL;
		$emptyPhpData="<"."?php".PHP_EOL;
		foreach($variable_ids as $var_id)
		{
			$defaultPhpData=$defaultPhpData."$".$var_id." = '".$data_of_variables[$var_id]['defaultValue']."';".PHP_EOL;
			$emptyPhpData=$emptyPhpData."$".$var_id." = '';".PHP_EOL;
		}
		$defaultPhpData =$defaultPhpData. "?".">";
		$emptyPhpData =$emptyPhpData. "?".">";
		
		$ftp_dir = str_replace($account_absolute_path, '', $dir);
		ftp_file_put_content($ftp_dir."/languagesInput/default.php", $defaultPhpData);
		ftp_file_put_content($ftp_dir."/languagesInput/empty.php", $emptyPhpData);
	}
	
	//Calculate empty ones
	$languagefile = $dir."/languagesInput/".($choosenListInputLang!="" ? $choosenListInputLang : $choosenAdminLang).".php";
	if(is_file($languagefile)) include($languagefile);
	
	foreach($variable_ids as $var_id)
	{
		if('' == ${$var_id}) $l_empty_input_language_variables++;
		//print('here<br/>'.$var_id.'-->'.$data_of_variables[$var_id]['defaultValue'].'<br/>');
		${$var_id}=$data_of_variables[$var_id]['defaultValue']; 
	}
	
	$languagefile = $dir."/languagesInput/empty.php";
	if(is_file($languagefile)) include($languagefile);
	$languagefile = $dir."/languagesInput/default.php";
	if(is_file($languagefile)) include($languagefile);
	if($show_choosen_language)
	{
		$languagefile = $dir."/languagesInput/".($choosenListInputLang!="" ? $choosenListInputLang : $choosenAdminLang).".php";
		if(is_file($languagefile)) include($languagefile);
	}
	
	foreach($variable_ids as $var_id)
	{
		$v_language_variables[$var_id] = ${$var_id}; 
	}
	
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_local_languagevariable_basisconfig WHERE module_name = '".$o_main->db->escape_str($module)."'");
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		${$v_row['languagevariable_name']} = $v_row['text'];
	}
	
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_local_languagevariable WHERE module_name = '".$o_main->db->escape_str($module)."'");
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		${$v_row['languagevariable_name']} = $v_row['text'];
	}
}