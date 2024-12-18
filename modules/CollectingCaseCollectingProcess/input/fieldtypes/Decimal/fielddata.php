<?php
$thisFieldType = 61;
$thisDatabaseField = "DECIMAL(11,2)";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is decimal number by default with maximum number of digits 11 and 2 positions for decimal fraction.

In <b>Extra value</b> it is possible to customize fieldtype to setting maximum number of digits <b>(M)</b> and decimal fraction <b>(D)</b> which should not be larger than <b>M</b>, separating them by comma.

For example, \"<b>20,4</b>\"

If decimal fraction is written longer in input, then that is rounded.");
?>