<?php
$thisFieldType = 0;
$thisDatabaseField = "LONGTEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is for dynamic text type input, where more input fields can be defined in one field and added/removed/sorted dynamicaly while editing content in such manner (fields)[:](display_type), where <b>(fields)</b> are defined as following:
<b>[Text to display before input]:[input name without spaces]:[lenght of the input]::[Next text to display before input]:[next input name]:[lenght of the input]</b> and so on.

And in <b>(display_type)</b> can be set values:
\t&bull; <b>rows</b> - which means that each field will be displayed on separate row
\t&bull; <b>columns</b> - which means that all fields will be shown in one row as columns

Default display_type is <b>rows</b>.

If <b>length</b> is set to <b>0</b> then it add a checkbox element instead of text input element

<b>Examples:</b>
\"Variant name:variantname:10[:]columns\" or
\"Article Nr:article_nr:10::Name:name:20::Price:price:5\"");