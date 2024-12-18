<?php
if($field[6][$langID] == "" and $access >= 10)
{
	if($field[11] != "")
	{
		if(strpos($field[11],"[") !== false && strpos($field[11],"]") > strpos($field[11],"["))
		{
			$format = explode("[",$field[11],2);
			list($format[1],$format[2]) = explode("]",$format[1],2);
			$format[1] = ereg_replace("[^0-9]", "", $format[1]);
		} else {
			$format = array("",ereg_replace("[^0-9]", "", $field[11]),"");
		}
		$sequence = intval($format[1]);
		
		if($sequence < 1) $sequence = 1;
		if(!$o_main->db->table_exists('sys_sequence'))
		{
			$b_table_created = $o_main->db->simple_query("CREATE TABLE sys_sequence (
				tablefield VARCHAR(50) NOT NULL,
				seq_format VARCHAR(50) NOT NULL,
				seq_num VARCHAR(50) NOT NULL,
				num INT NOT NULL,
				created TIMESTAMP NOT NULL,
				seq_status TINYINT NOT NULL,
				INDEX Idx (created, num, tablefield),
				INDEX Idx2 (seq_num)
			)");
			if(!$b_table_created)
			{
				echo $formText_RelationTableIsNotCreated_Fieldtype;
				return;
			}
		}
		$compFormat = $format[0]."[]".$format[2];
		$s_sql = "select num from sys_sequence where created < ADDDATE(NOW(), INTERVAL -1 HOUR) and tablefield = ? and seq_format = ? and seq_status = 1";
		$o_row = (object) array();
		$o_query = $o_main->db->query($s_sql, array($field[1], $compFormat));
		if($o_query && $o_query->num_rows()>0) $o_row = $o_query->row_array();
		if($o_row->num > 0) // if exists not used number
		{
			if($sequence <= $o_row->num)
			{
				$sequence = $o_row->num;
				$o_main->db->query("update sys_sequence set created = now() where num = $sequence and tablefield = ? and seq_format = ? and seq_status = 1", array($field[1], $compFormat));
			} else {
				$o_main->db->query("insert into sys_sequence(tablefield,seq_format,num,created,seq_status) values(?, ?, ?, NOW(), 1)", array($field[1], $compFormat, $sequence));
			}
		} else {
			// check next max sequence number
			$s_sql = "select max(num) num from sys_sequence where tablefield = ? and seq_format = ?";
			$o_row = (object) array();
			$o_query = $o_main->db->query($s_sql, array($field[1], $compFormat));
			if($o_query && $o_query->num_rows()>0) $o_row = $o_query->row_array();
			if($o_row->num > 0) // if exists not used number
			{
				if($sequence < ($o_row->num+1)) $sequence = $o_row->num+1;
			} else {
				//begining point of sequence
			}
			$o_main->db->query("insert into sys_sequence(tablefield,seq_format,num,created,seq_status) values(?, ?, ?, NOW(), 1)", array($field[1], $compFormat, $sequence));
		}
		$seqformat = '%1$s%2$0'.strlen($format[1]).'d%3$s';
		$field[6][$langID] = sprintf($seqformat,$format[0],$sequence,$format[2]);
		$o_main->db->query("update sys_sequence set seq_num = ? where num = ? and tablefield = ? and seq_format = ? and seq_status = 1", array($field[6][$langID], $sequence, $field[1], $compFormat));
	} else {
		// no format set
	}
}
if($access>=10)
{
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" /><?php
} else {
	print $field[6][$langID];
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" /><?php
}
?>