<?php
$options = explode(":",$fieldInfo[11]);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);
$o_main->db->query("DELETE FROM {$options[3]} WHERE {$options[4]} = ? and contentTable = ?", array($deleteFieldID, $deleteFieldTable));