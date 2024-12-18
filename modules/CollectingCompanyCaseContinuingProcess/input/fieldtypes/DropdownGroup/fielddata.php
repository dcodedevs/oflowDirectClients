<?php
$thisFieldType = 0;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is dropdown which takes values from other module (single language table) group field filtered by some input or not.

Link parameters to other module (table) should be written in <b>Extra value</b> as following<b> [current_module_filter_field]:[link_table_filter_field]:[link_table]:[link_table_group_field(as dropdown_value)]:[link table grouped fields which should be displayed in dropdown separated by comma]</b>.

For example, if you want to list contactPerson group in dropdown, then create module Customers and table \"customers\". Then add fields (contactPName, contactPEmail) and grouping field \"contactPerson\". And here write parameters in Extra field: \"customerID:id:customers: contactPerson:contactPName, contactPEmail\" (no spaces).
If there is no need for filter, then leave empty first value, but do not miss \":\". 

This field also have JS script which automatically refresh the data of dropdown if filter field is changed.");
