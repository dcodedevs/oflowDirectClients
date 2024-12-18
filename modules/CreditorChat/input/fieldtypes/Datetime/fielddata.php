<?php
$thisFieldType = 2;
$thisDatabaseField = "DATETIME";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is datetime field and can be set in any PHP convertable format by strtotime() function.
Default display format is <b>d.m.Y H:i:s</b>.

In DB it is saved based on server date parameters.
By default it is <b>Y-m-d H:i:s</b>.");
