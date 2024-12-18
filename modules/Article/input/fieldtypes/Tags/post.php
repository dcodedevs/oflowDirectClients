<?php
$o_main->db->query("delete from sys_tagrelation where contentID = ? and contentTable = ?", array($basetable->ID, $basetable->name));
$key = $fields[$nums][1]."_tagID";
if(array_key_exists($key,$_POST))
{
	foreach($_POST[$key] as $i=>$item)
	{
		$s_sql = "update sys_tag set `type` = 1 where id = ? and (`type` = 0 or `type` IS NULL)";
		$o_main->db->query($s_sql, array($item));
		$s_sql = "insert into sys_tagrelation(tagID, contentID, contentTable, sortnr) values(?, ?, ?, ?)";
		$o_main->db->query($s_sql, array($item, $basetable->ID, $basetable->name, $i));
	}
}
?>