<?php
// ALI - function check is needed because of reuse in account update!
if(!function_exists("get_files")) include(__DIR__.'/fnctn_get_files.php');
if(!function_exists("get_tables")) include(__DIR__.'/fnctn_get_tables.php');
if(!function_exists("get_table_fields")) include(__DIR__.'/fnctn_get_table_fields.php');
if(!function_exists("get_table_fields_from_db")) include(__DIR__.'/fnctn_get_table_fields_from_db.php');
if(!function_exists("get_table_fields_structure")) include(__DIR__.'/fnctn_get_table_fields_structure.php');
if(!function_exists("check_table_system_fields")) include(__DIR__.'/fn_check_table_system_fields.php');
if(!function_exists("get_table_indexes")) include(__DIR__.'/fn_get_table_indexes.php');
if(!function_exists("get_table_indexes_from_db")) include(__DIR__.'/fn_get_table_indexes_from_db.php');

$tables = get_tables(__DIR__.'/');
$table_names = array_keys($tables);

$table_structure = $v_index_structure = array();
foreach($table_names as $s_table)
{
	check_table_system_fields($s_table, __DIR__.'/../../', $module);
	$table_structure = get_table_fields_structure($s_table, __DIR__.'/../../', $table_structure);
	$v_index_structure = get_table_indexes($s_table, __DIR__.'/../../', $v_index_structure);
}

foreach($table_names as $s_table)
{
	if(strlen($s_table))
	{
		if(isset($v_sys_db_check[$s_table])) continue;
		$v_sys_db_check[$s_table] = 0;
		//check if multi-language
		$multilanguage = 0;
		$single_language_table = '';
		if(strrpos($s_table,'content')!==false) $single_language_table = substr($s_table,0,-7);
		if(strlen($single_language_table)>0 and in_array($single_language_table,$table_names)) $multilanguage = 1;
		
		if(isset($_GET['debug']))
		{
			print('TABLE: <strong>'.$s_table.'</strong><br />Multilanguage: '.$multilanguage.'<br />Parent table: '.$single_language_table.'<br /><br />');
			print('checking if exists table!<br />');
		}
		if(!$o_main->db->table_exists($s_table))
		{
			if(isset($_GET['debug']))
			{
				print('There is no table <strong>'.$s_table.'</strong><br />');
				print('creating table<br />');
			}
			$b_is_id = FALSE;
			$s_sql_create = $s_sql_field = $s_sql_index = '';
			if(isset($table_structure[$s_table]))
			{
				foreach($table_structure[$s_table] as $fieldFromFile)
				{
					if(strlen($s_sql_field)>0) $s_sql_field = $s_sql_field. ', '.PHP_EOL;
					if('id' == strtolower(trim($fieldFromFile['id']))) $b_is_id = TRUE;
					$s_sql_field .= $o_main->db_escape_name($fieldFromFile['id']).' '.$fieldFromFile['databaseType'].' '.($fieldFromFile['id']=='id' ? 'AUTO_INCREMENT' : $fieldFromFile['databaseTypeExtra']);
					
					if($fieldFromFile['id'] == 'origId' /*|| $fieldFromFile['id'] == 'origcontentId'*/
						|| ($multilanguage == 1 and $fieldFromFile['id'] == $single_language_table.'ID'))
					{
						$s_sql_index = $s_sql_index . ', INDEX '.$o_main->db_escape_name($fieldFromFile['id'].'Idx').' ('.$o_main->db_escape_name($fieldFromFile['id']).')';
					}
				}
			}
			$s_sql_create = 'CREATE TABLE '.$o_main->db_escape_name($s_table).' ( '.$s_sql_field.($b_is_id ? ', PRIMARY KEY(id)' : '').$s_sql_index.')';
			if(isset($_GET['debug']))
			{
				print ('<br />');
				print ($s_sql_create);
				print('<br />');
			}
			if($s_sql_field!='')
			{
				if(!$o_main->db->query($s_sql_create)) $error_msg['error_'.count($error_msg)] = '[DBCheck] Table '.$s_table.' not created: '.json_encode($o_main->db->error());
			}
		} else {
			if(isset($_GET['debug']))
			{
				print ('Database exist checking if all fields are in database!<br />');
			}
			$table_fields_from_db = $o_main->db->list_fields($s_table);
			if(isset($table_structure[$s_table]))
			{
				foreach($table_structure[$s_table] as $fieldFromFile)
				{
					if(in_array($fieldFromFile['id'], $table_fields_from_db))
					{
						if(isset($_GET['debug']))
						{
							print ($fieldFromFile['id'].' field exists!<br />');
						}
					} else {
						$s_sql_alter = 'ALTER TABLE '.$o_main->db_escape_name($s_table).' ADD COLUMN ('.$o_main->db_escape_name($fieldFromFile['id']).' '.$fieldFromFile['databaseType'].' '.$fieldFromFile['databaseTypeExtra'].')';
						
						if($fieldFromFile['id'] == 'origId' /*or $fieldFromFile['id'] == 'origcontentId'
							*/or ($multilanguage == 1 and $fieldFromFile['id'] == $single_language_table.'ID'))
						{
							$s_sql_alter .= ', ADD INDEX '.$o_main->db_escape_name($fieldFromFile['id'].'Idx').' ('.$o_main->db_escape_name($fieldFromFile['id']).')';
						}
						
						if(isset($_GET['debug']))
						{
							print ($fieldFromFile['id'].' there is no such field! Altering table<br />');
							print($s_sql_alter.'<br />');
						}
						if(!$o_main->db->query($s_sql_alter)) $error_msg['error_'.count($error_msg)] = '[DBCheck] Table '.$s_table.' not altered: '.json_encode($o_main->db->error());

					}
				}
			}
		}
		
		//check Indexes
		$v_index_type = array('unique' => 'UNIQUE ', 'fulltext' => 'FULLTEXT ', 'spatial' => 'SPATIAL ');
		$v_limited_types = array('text', 'mediumtext', 'longtext');
		$v_db_indexes = get_table_indexes_from_db($s_table);
		$v_db_fields = array();
		$o_fields = $o_main->db->field_data($s_table);
		foreach($o_fields as $o_field)
		{
			$v_db_fields[$o_field->name] = $o_field->type;
		}
		if(isset($v_index_structure[$s_table]))
		{
			foreach($v_index_structure[$s_table] as $v_index)
			{
				if(array_key_exists($v_index[0], $v_db_indexes))
				{
					$b_create_index = false;
					foreach($v_index[3] as $l_x => $s_column)
					{
						$l_y = array_search($s_column, $v_db_indexes[$v_index[0]]['fields']);
						if($l_y === false || $l_x != $l_y)
						{
							$b_create_index = true;
							break;
						}
					}
					if(count($v_index[3]) != count($v_db_indexes[$v_index[0]]['fields'])) $b_create_index = true;
					if($v_db_indexes[$v_index[0]]['uniq'] && $v_index[1] != 'unique') $b_create_index = true;
					if(!$v_db_indexes[$v_index[0]]['uniq'] && $v_index[1] == 'unique') $b_create_index = true;
					if($b_create_index)
					{
						foreach($v_index[3] as $l_x => $s_column)
						{
							$v_index[3][$l_x] = $o_main->db_escape_name($v_index[3][$l_x]);
							if(in_array($v_db_fields[$s_column], $v_limited_types)) $v_index[3][$l_x] = $v_index[3][$l_x].'(100)';
						}
						$s_sql = 'ALTER TABLE '.$o_main->db_escape_name($s_table).' DROP INDEX '.$o_main->db_escape_name($v_index[0]);
						if(!$o_main->db->query($s_sql)) $error_msg['error_'.count($error_msg)] = '[DBCheck] Index '.$v_index[0].' not cleaned before update: '.json_encode($o_main->db->error());
						if($v_index[1] == 'primary')
						{
							$s_sql = 'ALTER TABLE '.$o_main->db_escape_name($s_table).' ADD PRIMARY KEY ('.implode(',', $v_index[3]).')';
						} else {
							$s_sql = 'ALTER TABLE '.$o_main->db_escape_name($s_table).' ADD '.$v_index_type[$v_index[1]].'INDEX '.$o_main->db_escape_name($v_index[0]).' ('.implode(',', $v_index[3]).')';
						}
						if(!$o_main->db->query($s_sql)) $error_msg['error_'.count($error_msg)] = '[DBCheck] Index '.$v_index[0].' not created: '.json_encode($o_main->db->error());
					}
				} else {
					foreach($v_index[3] as $l_x => $s_column)
					{
						$v_index[3][$l_x] = $o_main->db_escape_name($v_index[3][$l_x]);
						if(in_array($v_db_fields[$s_column], $v_limited_types)) $v_index[3][$l_x] = $v_index[3][$l_x].'(100)';
					}
					if($v_index[1] == 'primary')
					{
						$s_sql = 'ALTER TABLE '.$o_main->db_escape_name($s_table).' ADD PRIMARY KEY ('.implode(',', $v_index[3]).')';
					} else {
						$s_sql = 'ALTER TABLE '.$o_main->db_escape_name($s_table).' ADD '.$v_index_type[$v_index[1]].'INDEX '.$o_main->db_escape_name($v_index[0]).' ('.implode(',', $v_index[3]).')';
					}
					if(!$o_main->db->query($s_sql)) $error_msg['error_'.count($error_msg)] = '[DBCheck] Index '.$v_index[0].' not created: '.json_encode($o_main->db->error());
				}
			}
		}
	}
}
?>