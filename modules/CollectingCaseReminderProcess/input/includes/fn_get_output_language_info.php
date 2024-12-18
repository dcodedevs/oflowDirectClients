<?php
function get_output_language_info($s_module = NULL)
{
	$o_main = get_instance();
	
	$l_empty_output_language_variables = 0;
	$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC');
	if($o_query && $o_row = $o_query->row())
	{
		$s_default_output_language = $o_row->languageID;
		
		if(NULL == $s_module)
		{
			$s_module_absolute_path = realpath(__DIR__.'/../../');
		} else {
			$s_module_absolute_path = BASEPATH.$s_module;
		}
		if(!function_exists('get_form_text_variables')) include_once(__DIR__.'/fnctn_get_form_text_variables.php');
		if(!function_exists('get_files')) include_once(__DIR__.'/fnctn_get_files.php');
		if(!function_exists('get_language_variables')) include_once(__DIR__.'/fnctn_get_language_variables.php');
		
		$v_scan = scandir($s_module_absolute_path);
		foreach($v_scan as $s_folder)
		{
			if(strtolower(substr($s_folder,0,6)) == 'addon_' || in_array($s_folder, array('input', 'properties', '.', '..')))
			{
			} else {
				$b_check_subfolders = 1;
				$v_folders = array($s_module_absolute_path.'/'.$s_folder.'/');
				$v_extensions = array('php');
				$v_except_folders = array();
				
				$v_files = get_files($v_folders, $v_extensions, $v_except_folders, $b_check_subfolders);
				$v_variables = get_language_variables($v_files);
				$v_variables_ids = array_keys($v_variables);
				
				$s_include = $s_module_absolute_path.'/'.$s_folder.'/languagesOutput/'.$s_default_output_language.'.php';
				if(is_file($s_include)) include($s_include);
				
				foreach($v_variables_ids as $s_id)
				{
					if('' == ${$s_id}) $l_empty_output_language_variables++;
				}
			}
		}
	}
	
	return array(
		'empty_output_language_variables' => $l_empty_output_language_variables,
	);
}