<?php
function get_table_indexes($s_table, $s_module_path, $v_indexes)
{
	$v_index_types = array();
	$s_index_file = $s_module_path."/input/settings/indexes/".$s_table.".php";
	if(is_file($s_index_file))
	{
		include($s_index_file);
		foreach($sys_table_indexes as $l_key => $v_index)
		{
			$v_tmp = explode(":", $v_index);
			$v_tmp[3] = explode(",", $v_tmp[3]);
			$v_indexes[$v_tmp[2]][$l_key] = $v_tmp;
		}
	}
	return $v_indexes;
}
?>