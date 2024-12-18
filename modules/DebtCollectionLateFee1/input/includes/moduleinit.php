<?php
$moduleID = 0;
$activateSeo = '0';
$o_query = $o_main->db->get_where('moduledata', array('name' => $module));
if($o_query && $o_row = $o_query->row())
{
	$moduleID = $o_row->id;
	if(isset($moduledatatype) && $o_row->type != $moduledatatype) $o_main->db->update('moduledata', array('type' => $moduledatatype), array('name' => $module));
}
$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
if($o_query)
{
	foreach($o_query->result() as $o_row)
	{
		$languageEnding[] = $o_row->languageID;
		$languageName[$o_row->languageID] = $o_row->name;
	}
}
if(isset($_GET['relation_module_id']) && is_numeric($_GET['relation_module_id']))
{
	$moduleID = $_GET['relation_module_id'];
}
if(isset($_GET['submodule']))
{
	$submodule = $o_main->db_escape_name($_GET['submodule']);
}
if(isset($_GET['ID']))
{
	$ID = $_GET['ID'];
}
if(isset($_GET['level']) && is_numeric($_GET['level']))
{
	$level = $_GET['level'];
} 
if(isset($_GET['parentID']))
{
	$parentID = $_GET['parentID'];
}
$s_input_jumpfirst_link = '';
if(isset($jumpfirstpage) && $jumpfirstpage == 1 && !isset($_GET['includefile']) && $variables->developeraccess == 0 && $o_main->db->table_exists($submodule))
{
	if(!function_exists('get_curent_GET_params')) include(__DIR__.'/fnctn_get_curent_GET_params.php');
	$v_param = array();
	if($o_main->db->field_exists('moduleID', $submodule)) $v_param = array('moduleID' => $moduleID);
	$o_query = $o_main->db->get_where($submodule, $v_param);
	if($o_query && $o_row = $o_query->row())
	{
		$s_input_jumpfirst_link = $_SERVER['PHP_SELF'].'?'.get_curent_GET_params(array('ID', 'includefile')).'&includefile=edit&ID='.(isset($o_row->id) ? $o_row->id : $o_row->ID);
	}
}

include(__DIR__.'/fieldloader.php');
foreach($databases as $basetable)
{
	if($basetable->multilanguage == 0)
	{
		$o_query = $o_main->db->get_where($basetable->name, array('id' => $ID));
		if($o_query && $o_row = $o_query->row())
		{
			$basetable->ID = $o_row->id;
			foreach($basetable->fieldNums as $nums)
			{				    
				$fields[$nums][8] = array('all');
				$fields[$nums][6] = array();
				$fields[$nums][6]['all'] = $o_row->{$fields[$nums][0]};
				if($fields[$nums][4] == 20 && $fields[$nums][6]['all'] != 0 && $fields[$nums][6]['all'] != '')
				{
					$parentID = $fields[$nums][6]['all'];
					$parentmodule = $fields[$nums][5];
				}
			}
			
			// check owner access
			if($b_owner_access && $o_row->createdBy != $variables->loggID)
			{
				$access = $access % 10;
			}
		} else {
			foreach($basetable->fieldNums as $nums)
			{
				$fields[$nums][8] = array('all');
			}
		}	 
	} else {
		foreach($basetable->fieldNums as $nums)
		{
			$fillArray = array();
			$fields[$nums][6] = array();
			for($e = 1; $e < sizeof($languageEnding); $e++)
			{
				$fillArray[] = $languageEnding[$e];
				$o_query = $o_main->db->get_where($basetable->name, array($basetable->connection.'ID' => $ID, $basetable->connection.'ID !=' => 0, 'languageID' => $languageEnding[$e]));
				if($o_query && $o_row = $o_query->row())
				{
					$basetable->ID = $o_row->id;
					$fields[$nums][6][$languageEnding[$e]] = stripslashes($o_row->{$fields[$nums][0]});
				} else {
					$fields[$nums][6][$languageEnding[$e]] = '';
				}
			}
			$fields[$nums][8] = $fillArray;
		}
	}
}
foreach($fields as $key => $field)
{
	// generate IDs
	foreach($field[8] as $x => $langID)
	{
		$ui_id_counter++;
		$ending = $langID;
		if($ending == 'all') $ending = '';
		$fields[$key]['ui_id'.$ending] = $field['ui_id'.$ending] = $fields_replace['[:replace:'.$field[0].$ending.']'] = $field[1].'_'.$ui_editform_id.$ui_id_counter;
	}
	// update structure variable
	$fieldsStructure[$field[0]] = $field;
}
