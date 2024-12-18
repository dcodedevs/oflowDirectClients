<?php
// this script is called before deleting content/subcontent from content list.
$o_main->db->query("delete sys_htaccess from sys_htaccess inner join pageID on sys_htaccess.pageID = pageID.id where pageID.contentID = ? and pageID.contentTable = ?", array($deleteFieldID, $deleteFieldTable));

$o_main->db->query("delete pageIDcontent from pageIDcontent inner join pageID on pageIDcontent.pageIDID = pageID.id where pageID.contentID = ? and pageID.contentTable = ?", array($deleteFieldID, $deleteFieldTable));
?>