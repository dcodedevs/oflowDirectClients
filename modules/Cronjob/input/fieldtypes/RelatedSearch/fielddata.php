<?php
$thisFieldname = "Textfield";
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is for adding relation from external table and saved down in link table. In extra field should be specified such parameters:
<b>[External table name]:[External table id]:[External table field which will be displayed in dropdown]:[Link table name]:[Link table relation ID name]:[Link table external tables ID name]:[Enable save content local as JSON (1 = optional, otherwise it stores in related table)]:[extrafield]:[extrafield]:[extrafield]</b>.

<b>Important:</b> Link table is created automatically!

<b>Example:</b><br>\"partnercategory:id:name: partnercategorylink:partnerID:categoryID:1:stock\"");
?>