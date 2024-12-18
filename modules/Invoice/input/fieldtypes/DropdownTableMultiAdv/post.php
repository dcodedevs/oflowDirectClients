<?php
$options = explode(":",$fields[$nums][11]);
if($fields[$nums][9] != 1 && $fields[$nums][10] != 1)
{
	$dropdown_table		= $o_main->db_escape_name($options[0]);
	$dropdown_id		= $o_main->db_escape_name($options[1]);
	$dropdown_name		= $o_main->db_escape_name($options[2]);
	$rel_table			= strtolower($o_main->db_escape_name($options[3]));
	$rel_content_field	= $o_main->db_escape_name($options[4]);
	$rel_dropdown_field	= $o_main->db_escape_name($options[5]);
	$rel_input_field	= $o_main->db_escape_name($options[6]);
	$rel_dropdown_label	= ucwords(strtolower($options[7]));
	$rel_input_label	= ucwords(strtolower($options[8]));
	
	
	$o_main->db->query("DELETE FROM ".$rel_table." WHERE ".$rel_content_field." = ?", array($basetable->ID));
	foreach($_POST[$fields[$nums][1]] as $l_key => $l_relation_id)
	{
		$l_relation_id = intval($l_relation_id);
		$s_input = $_POST[$fields[$nums][1]."_input"][$l_key];
		if($s_input && $l_relation_id > 0)
		{
			if($o_main->db->query("SELECT * FROM ".$rel_table." WHERE ".$rel_content_field." = ? AND ".$rel_dropdown_field." = ?", array($basetable->ID, $l_relation_id))->num_rows() == 0)
			{
				$o_main->db->query("INSERT INTO ".$rel_table." (".$rel_content_field.", ".$rel_dropdown_field.", ".$rel_input_field.") VALUES (?,?,?)", array($basetable->ID, $l_relation_id, $s_input));
			} else {
				$o_main->db->query("UPDATE ".$rel_table." SET ".$rel_input_field." = ? WHERE ".$rel_content_field." = ? AND ".$rel_dropdown_field." = ?", array($s_input, $basetable->ID, $l_relation_id));
			}
		}
	}
}
?>