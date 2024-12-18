<?php
$options = explode(":",$fieldInfo[11]);

$dropdown_table		= $o_main->db_escape_name($options[0]);
$dropdown_id		= $o_main->db_escape_name($options[1]);
$dropdown_name		= $o_main->db_escape_name($options[2]);
$rel_table			= strtolower($o_main->db_escape_name($options[3]));
$rel_content_field	= $o_main->db_escape_name($options[4]);
$rel_dropdown_field	= $o_main->db_escape_name($options[5]);
$rel_input_field	= $o_main->db_escape_name($options[6]);
$rel_dropdown_label	= ucwords(strtolower($options[7]));
$rel_input_label	= ucwords(strtolower($options[8]));

$o_query = $o_main->db->query("DELETE FROM ".$rel_table." WHERE ".$rel_content_field." = ?", array($ID));
