<?php
$thisFieldType = 60;
$thisDatabaseField = "CHAR(30)";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is sequence number which is filled with next continuous number based on <b>format</b> or it is free editable for user. Specify format in Extra field in such manner:
Sequence number should be placed in square brackets <b>\"[]\"</b> and<br><b>-</b> by zeroes [000000] can specify that on left side will be leading zeroes if number is smaller (example, 000012);
\t\t- setting other numbers means that sequence begin from this number;
\t\t- and it is possible to add other characters in format.

If Extra field is empty, user is allowed to enter any number in content input.

<b>Example:</b> Sequence format \"PROD_[00000]\" will produce value like \"PROD_00001\" or sequence format \"[100]\" will produce value first value 100 and then continues conting up.

Limited to 30 chars with prefix.");
?>