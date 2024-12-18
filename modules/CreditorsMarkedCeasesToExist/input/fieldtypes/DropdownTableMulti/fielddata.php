<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is multi select dropdown where values are taked from external table and saved down in link table. In extra field should be specified such parameters:
<b>[External table name]:[External table id]:[External table field which will be displayed in dropdown]:[Link table name]:[Link table relation ID name]:[Link table external tables ID name]</b>.

<b>Important:</b> Link table is created automatically!

Multi-language table support available.

<b>Example:</b><br>\"partnercategory:id:name: partnercategorylink:partnerID:categoryID\"");
?>