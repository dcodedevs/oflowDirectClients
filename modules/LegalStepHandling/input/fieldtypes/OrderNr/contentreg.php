<?php
if(array_key_exists($fieldName,$_POST) and intval($_POST[$fieldName])>0)
{
	$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
} else {
	$l_num = 0;
	$sql = "SELECT MAX(".$o_main->db_escape_name($fields[$fieldPos][0]).") max_num FROM ".$o_main->db_escape_name($fields[$fieldPos][3])." WHERE moduleID = ?";
	$o_query = $o_main->db->query($sql, array($_POST['moduleID']));
	if($o_query && $o_row = $o_query->row()) $l_num = $o_row->max_num;
	if($l_num > 0) $fields[$fieldPos][6][$this->langfields[$a]] = $l_num + 1;
	else $fields[$fieldPos][6][$this->langfields[$a]] = 1;
}
