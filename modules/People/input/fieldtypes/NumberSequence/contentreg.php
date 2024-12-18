<?php
$o_main->db->query("update sys_sequence set seq_status = 2 where seq_num = ? and tablefield = ? and seq_status = 1", array($_POST[$fieldName], $fields[$fieldPos][1]));

$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
?>