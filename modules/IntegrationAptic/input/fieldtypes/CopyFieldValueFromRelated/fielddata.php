<?php
$thisFieldType = 60;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is used to copy value from relation (parent) table field. Relation should be specified in <b>Extra value</b> as following:
<b>[related_table_name]:[related_table_field_for_copying]:[current_table_relation_field]</b>

For example, if you need to copy Discount from Customer to Order, then write like this \"customers:discount:customerId\"");
