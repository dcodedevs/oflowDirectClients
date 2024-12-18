<?php
function get_tables($pre_path)
{
	$pre_path=$pre_path."../settings/";
	$tmp_return = array();
	$extensions=array('php');
	$except_dirs=array();
	$check_subdirs=0;
	$where_to_look=array($pre_path."tables");
	
	$table_files=get_files($where_to_look, $extensions, $except_dirs, $check_subdirs);
	
	foreach($table_files as $table_file) 
	{
		$table_name =trim( str_replace(".php", "",basename($table_file)) );
		include($table_file);
		if(isset($mysqlTableName))
		{
			foreach($mysqlTableName as $table_name)
			{
				$table_name=explode(":", $table_name);
				$table_name=$table_name[0];
				$tmp_return[$table_name]=$table_file;
			}
			unset($mysqlTableName);
		}
	}
	return $tmp_return;
}
?>