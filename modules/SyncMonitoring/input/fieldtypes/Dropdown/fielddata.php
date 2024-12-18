<?php
$thisFieldType = 0;
$thisDatabaseField = "CHAR(100)";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is dropdown where list is defined at field creation in Extra Field in such manner:
[value_saved_in_db]:[value_displayed_in_admin]::[next_value_saved_in_db]:[next_value_displayed_in_admin] and so on.

For example, \"1:One::2:Two::3:Three\".");
?>