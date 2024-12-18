<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is multi DropdownTable + input_field, used as follows:
<b>[dropdown_table]:[dropdown_table_id]:[dropdown_table_name]:[relation_table]:[relation_field_to_content]:[relation_field_to_dropdown]:[input_field]:[dropdown_field_label]:[input_field_label]</b>
<br />
For example:
<b>size:id:name:productsize:product_id:size_id:quantity:Size:Qty</b>
<br />
The relation table is created automatically.");
?>