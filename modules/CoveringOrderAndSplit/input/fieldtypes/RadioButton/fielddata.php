<?php
$thisFieldType = 0;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is radiobutton where list is defined at field creation in Extra Field in such manner:
[<b>choice1</b>]:[<b>choice2</b>]:[<b>choice3</b>] and so on.

For example, \"One:Two:Three\".");
?>