<?php
function get_layout_css($o_main, &$v_return = array(), $l_layout_id, $l_parent_id = 0)
{
	$o_query = $o_main->db->query('SELECT lb.*, m.name FROM sys_layoutblock lb LEFT OUTER JOIN moduledata m ON m.uniqueID = lb.assigned_module_id WHERE layout_id = ? AND parent = ? ORDER BY sortnr ASC', array($l_layout_id, $l_parent_id));
	if($o_query && $o_query->num_rows() > 0)
	{
		foreach($o_query->result() as $o_row)
		{
			if($o_row->assigned_module_output == '') $o_row->assigned_module_output = 'output';
			$s_file = '/modules/'.$o_row->name.'/'.$o_row->assigned_module_output.'/output.css';
			if($o_row->assigned_module_id > 0 && is_file(__DIR__.'/..'.$s_file)) $v_return[] = $s_file;
			
			$s_sql = 'SELECT * FROM sys_layoutblock WHERE layout_id = ? AND parent = ? ORDER BY sortnr ASC';
			$o_query_child = $o_main->db->query($s_sql, array($l_layout_id, $o_row->id));
			if($o_query_child->num_rows() > 0)
			{
				get_layout_css($o_main, $v_return, $l_layout_id, $o_row->id);
			}
		}
	}
	
	return $v_return;
}
?>