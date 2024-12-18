<?php
$thisFieldType = 0;
$thisDatabaseField = "CHAR(1)";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is checkbox list where values are taken from external table and saved down in link table. In extra field should be specified such parameters:
<b>[External table name]:[External table id]:[External table field which will be displayed in dropdown]:[Link table name]:[Link table relation ID name]:[Link table external tables ID name]:[parent id]:[filter ids]:[store all levels 0 or 1]</b>.

<b>Important:</b> Link table is created automatically!

ParentId and filterID is optional to make tree structure. Filter ids should be number separated by comma (pointing to parent). Store_all_levels by default is not set. Can be used in tree structure to store all parent levels if some child is selected.

<b>Example:</b><br>\"partnercategory:id:name: partnercategorylink:partnerID:categoryID\".

<b>Important:</b> Mandatory field functionality cannot be applyed for this field!");
?>