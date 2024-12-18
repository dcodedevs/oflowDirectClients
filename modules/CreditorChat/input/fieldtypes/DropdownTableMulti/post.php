<?php
$options = explode(":",$fields[$nums][11]);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);
$tablename = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);
										
$o_main->db->query("DELETE FROM {$options[3]} WHERE {$options[4]} = ? and contentTable = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."", array($basetable->ID, $tablename));
foreach($_POST[$fields[$nums][1]] as $item)
{
	$sql = "INSERT INTO {$options[3]}({$options[4]},{$options[5]},contentTable".($o_main->multi_acc?", account_id":"").") VALUES(?,?,?".($o_main->multi_acc?", '".$o_main->db->escape_str($o_main->account_id)."'":"").");";
	$o_main->db->query($sql, array($basetable->ID, $item, $tablename));
}
