<?php
$thisFieldType = 0;
$thisDatabaseField = "CHAR(255)";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is for existing same table field (which type of GroupField) grouping as array. While editing, there is possible to add and remove array elements dynamically. To use this grouping fill in Extra field with:
<b>[Text to display before input]:[existing table field name]:[width of the input]::[Next text to display before input]:[next existing field name]:[width of the input]</b> and so on.

After that separated with <b>\":::\"</b> can be set field <b>parameters</b> separated each by <b>\"::\"</b>

<b>Field parameters:</b>
<b>NOTOOLBOX</b> - will not be displayed \"ADD/DELETE/SETDEFAULT\" buttons;

<b>Examples:</b>
\"First name:firstname:10::Last name:lastname:20\" or \"First name:firstname:10::Last name:lastname:20:::NOTOOLBOX\"");
