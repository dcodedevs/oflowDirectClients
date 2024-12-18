<?php
function save_checkboxes_from_external_table($options, $parentID, $values)
{
	$o_main = get_instance();
	$s_sql = 'SELECT c.'.$options[1].' col0, c.'.$options[6].' col1 FROM '.$options[0].' c WHERE c.'.$options[1].' = ? AND content_status < 3';
	
	$o_query = $o_main->db->query($s_sql, array($parentID));
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result_array() as $row)
		{
			if(!in_array($row['col0'],$values))
			{
				$values[] = $row['col0'];
				if($row['col1'] > 0)
					$values = save_checkboxes_from_external_table($options, $row['col1'], $values);
			}
		}
	}
	return $values;
}
?>