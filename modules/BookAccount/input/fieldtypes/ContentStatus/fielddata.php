<?php
$thisFieldType = 0;
$thisDatabaseField = "TINYINT(2)";
$thisDatabaseFieldExtra = "NOT NULL";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is system field for content status.

In DB will be stored following values:
	0 - active content
	1 - inactive content
	2 - marked as deleted content
	3 - history record.");
?>