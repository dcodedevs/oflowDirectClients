<?php
$thisFieldType = 0;
$thisDatabaseField = "LONGTEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is for dynamic text type input, where more input fields can be defined in one field and added/removed/sorted dynamicaly while editing content in such manner:
<b>[Text to display before input]:[input name without spaces]:[lenght of the input]::[Next text to display before input]:[next input name]:[lenght of the input]</b> and so on.

<b>Examples:</b>
\"Variant name:variantname:10\" or
\"Article Nr:article_nr:10::Name:name:20::Price:price:5\"");
?>