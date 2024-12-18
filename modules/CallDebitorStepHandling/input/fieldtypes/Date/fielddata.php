<?php
$thisFieldType = 2;
$thisDatabaseField = "DATE";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is date in format <b>DD.MM.YYYY</b>.

In DB it is saved based on server date parameters.
By default it is <b>YYYY-MM-YY</b>.");
