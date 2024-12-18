<?php
if(!function_exists('get_all_languages')){
function get_all_languages()
{
	$v_return = array();
	$o_main = get_instance();
	$o_query = $o_main->db->query('SELECT * FROM language ORDER BY sortnr ASC');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			$v_return[$o_row->languageID] = $o_row->name;
		}
	}
	return $v_return;
}
}
if(!function_exists('getOutputLanguages')){
function getOutputLanguages()
{
	$l_i = 0;
	$v_return = array();
	$o_main = get_instance();
	$o_query = $o_main->db->query('SELECT * FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			$v_return[$o_row->languageID]['name'] = $o_row->name;
			$v_return[$o_row->languageID]['default'] = $o_row->defaultOutputlanguage;
			$v_return['default'][$l_i] = $o_row->languageID;
			$l_i++;
		}
	}
	return $v_return;
}
}
if(!function_exists('getInputLanguages')){
function getInputLanguages()
{
	$l_i = 0;
	$v_return = array();
	$o_main = get_instance();
	$o_query = $o_main->db->query('SELECT * FROM language WHERE inputlanguage = 1 ORDER BY defaultInputlanguage DESC, sortnr ASC');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			$v_return[$o_row->languageID]['name'] = $o_row->name;
			$v_return[$o_row->languageID]['default'] = $o_row->defaultInputlanguage;
			$v_return['default'][$l_i] = $o_row->languageID;
			$l_i++;
		}
	}
	return $v_return;
}
}