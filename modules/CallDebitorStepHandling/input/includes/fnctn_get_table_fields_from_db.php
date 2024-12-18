<?php
if(!function_exists('get_table_fields_from_db')){
function get_table_fields_from_db($s_table, $b_get_type = false)
{
	$v_return = array();
	$o_main = get_instance();
	if($o_main->db->table_exists($s_table))
	{
		if($b_get_type)
		{
			$o_fields = $o_main->db->field_data($s_table);
			foreach($o_fields as $o_field)
			{
				$v_return[$o_field->name] = $o_field->type;
			}
		} else {
			$v_return = $o_main->db->list_fields($s_table);
		}
	}
	return $v_return;
}
}