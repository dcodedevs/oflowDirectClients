<?php
$thisFieldType = 12;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array("<br />","&nbsp;&nbsp;"),
"This field is menu dropdown list, where it is possible to assign menu item for content.

Optionaly it is possible to specify is there need to copy initial value from parent or make default menu point. Then you need to append in Extra field \"[parent-table-name]\" or fill in menulevel ID (integer type allowed).

Example: <b>parentpage</b> or <b>45</b>");
