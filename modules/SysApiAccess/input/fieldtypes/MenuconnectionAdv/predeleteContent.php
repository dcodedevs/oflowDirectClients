<?php
// this script is called before deleting content/subcontent from content list.
mysql_query("delete sys_htaccess from sys_htaccess inner join pageID on sys_htaccess.pageID = pageID.id where pageID.contentID = '$deleteFieldID' and pageID.contentTable = '{$deleteFieldTable}'");

mysql_query("delete pageIDcontent from pageIDcontent inner join pageID on pageIDcontent.pageIDID = pageID.id where pageID.contentID = '$deleteFieldID' and pageID.contentTable = '{$deleteFieldTable}'");
?>