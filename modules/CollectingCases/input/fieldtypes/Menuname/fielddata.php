<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is single line text field for menu module to define level name in one or more languages.
On field change SEO url is updated.
Menu delete handler feature exists.

To save levelname in SEO table write <b>1</b> in Extra value field. If menu table have field seoDescription, then it is also saved.");
?>