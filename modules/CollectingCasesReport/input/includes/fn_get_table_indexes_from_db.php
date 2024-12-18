<?php
function get_table_indexes_from_db($s_table)
{
	$o_main = get_instance();
	$v_return = array();
	
	if($o_main->db->table_exists($s_table))
	{
		$o_query = $o_main->db->query('SHOW INDEX FROM '.$s_table);
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result() as $o_row)
			{
				$l_seq = intval($o_row->Seq_in_index) - 1;
				$v_return[$o_row->Key_name]["uniq"] = $o_row->Non_unique == 0;
				$v_return[$o_row->Key_name]["fields"][$l_seq] = $o_row->Column_name;
				$v_return[$o_row->Key_name]["lenght"][$l_seq] = $o_row->Sub_part;
			}
		}
	}
	return $v_return;
}
?>