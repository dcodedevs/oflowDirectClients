<?php
$o_main->db->query("delete from sys_tagrelation where contentID = ? and contentTable = ?", array($deleteFieldID, $deleteFieldTable));
?>