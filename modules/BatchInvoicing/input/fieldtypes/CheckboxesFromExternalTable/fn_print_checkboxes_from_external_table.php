<?php
function print_checkboxes_from_external_table($i, $s_sql, $options, $parentID, $field, $ending, $access)
{
	$o_main = get_instance();
	$x = $i;
	$s_sql_where = "";
	if(isset($options[6]) and strlen($options[6])>0)
	{
		$s_sql_where = ' WHERE c.'.$options[6].' = '.$o_main->db->escape($parentID);
	}
	
	$o_query = $o_main->db->query($s_sql.$s_sql_where, $v_param);
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result_array() as $row)
		{
			if($field[9] == 1)
			{
				if($row['col2']>0)
				{
					?><input type="hidden" name="<?php echo $field[1].$ending;?>[]" value="<?php echo htmlspecialchars($row['col0']);?>"><?php
				}
				$x = print_checkboxes_from_external_table($i+1, $s_sql, $options, $row['col0'], $field, $ending, $access);
			} else {
				?><div class="space-left">
					<input type="checkbox" name="<?php echo $field[1].$ending;?>[]" value="<?php echo htmlspecialchars($row['col0']);?>"<?php echo ($access<10||$field[10]==1?' readonly':'').($row['col2']>0?' checked':'');?>>
					<label><?php echo $row['col1'];?></label><?php
					$x = print_checkboxes_from_external_table($i+1, $s_sql, $options, $row['col0'], $field, $ending, $access);
				?></div><?php
			}
		}
	}
	return $x;
}
?>