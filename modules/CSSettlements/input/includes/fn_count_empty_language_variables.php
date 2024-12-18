<?php
ini_set('opcache.revalidate_freq', 0);
// function for geting list of files by extension
if(!function_exists("get_files")) include_once(__DIR__.'/fnctn_get_files.php');
// function for geting list of files by extension
if(!function_exists("get_dirs")) include_once(__DIR__.'/fnctn_get_dirs.php');
// function for getting list of all languages form database
if(!function_exists("getOutputLanguages")) include_once (__DIR__.'/fnctn_get_all_languages.php');
// function for geting current script GET params as string
if(!function_exists("get_curent_GET_params")) include_once(__DIR__.'/fnctn_get_curent_GET_params.php');
// function for geting list of language variables used in scripts
if(!function_exists("get_language_variables")) include_once(__DIR__.'/fnctn_get_language_variables.php');
// function for geting list of accesselement variables used in scripts
if(!function_exists("get_accesselement_variables")) include_once(__DIR__.'/fnctn_get_accesselement_variables.php');
// function for geting list of variables from file
if(!function_exists("get_form_text_variables")) include_once(__DIR__.'/fnctn_get_form_text_variables.php');
if(!function_exists("devide_by_uppercase")) include_once(__DIR__.'/fnctn_devide_by_upercase.php');
if(!function_exists("include_local")) include_once(__DIR__.'/fn_include_local.php');

if(!function_exists('count_empty_language_variables')){
function count_empty_language_variables()
{
	$o_main = get_instance();
	
	$l_empty_count = 0;
	$addonFolders = array();
	$scan = scandir(__DIR__."/../..");
	foreach($scan as $file)
	{
		if(strtolower(substr($file,0,6)) == 'addon_')
			$addonFolders[] = $file;
	}
	
	$data_of_variables=array();
	$data_of_accesselement_variables=array();
	$variable_ids=array();
	$accesselement_variable_ids=array();
	
	//gets php files for searching the language variables
	//directory  where to look for php scripts
	$dirs=array(realpath(__DIR__."/../.."));
	//define extension of the files
	$extensions=array('php');
	//directory exceptions
	$except_dirs=array(realpath(__DIR__."/../../input"), realpath(__DIR__."/../../properties"));
	foreach($addonFolders as $addonFolder) $except_dirs[] = realpath(__DIR__."/../../$addonFolder");
	//should check subdirs
	$check_subdirs=1;
	$output_folders=get_dirs($dirs, $except_dirs,0);
	
	$language_files = array();
	// language ids
	$lang_ids=array();
	// current values of the langauge variables
	$current_values=array();
	
	//gets ids for languages
	foreach($output_folders as $output_dir)
	{
		$directory_id=basename($output_dir);
		array_push($except_dirs, $output_dir.'/'.'languagesOutput');

		$language_files[$output_dir]=get_files(array($output_dir), $extensions, $except_dirs, $check_subdirs);

		if(count($language_files[$output_dir])>0)
		{
			$data_of_variables[$output_dir]=get_language_variables($language_files[$output_dir]);
			$variable_ids[$output_dir]=array_keys($data_of_variables[$output_dir]);
		}

		//gets the info about used wariables in the scripts
		//$o_query = $o_main->db->query("SELECT languageID FROM language WHERE outputlanguage = 1");
		$o_query = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC LIMIT 1");
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_language)
		{
			$language_values = include_local($output_dir.'/'."languagesOutput/".$v_language['languageID'].".php");
			foreach($variable_ids[$output_dir] as $variable_identificator)
			{
				if('' == trim($language_values[$variable_identificator])) $l_empty_count++;
			}
		}
	}
	
	return $l_empty_count;
}
}