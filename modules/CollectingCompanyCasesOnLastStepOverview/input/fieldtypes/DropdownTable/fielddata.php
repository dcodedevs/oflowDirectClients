<?php
$thisFieldType = 0;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is dropdown which takes values from other module. Link parameters to module (table) should be specified in <b>Extra value</b> as following: <b>[table_name]:[id]:[field_name_to_display]:[filter]:[parent_id]:[limit_to_level]:[none_option]:[skip_account_id_filter]</b>

[id] all the time will be just \"id\" (platform default). In <b>field_name_to_display</b> place can write more than one display field separated by comma. <b>Filter</b> is not obligate, and there need to write SQL condition after WHERE clause. Parent_id is optional and used to display tree output in dropdown and limit_to_level is also optional for limiting tree deep (1 = yes, 0 = no). Deep depends from ".'$'."_GET[level] variable. Set [none_option] to 1 if needed extra none value in dropdown as first element (optional). Set [skip_account_id_filter] to 1 if source table does not have account_id or it should be skipped in special cases (optional).

For example, if you want to list countries in dropdown, then create module Countries and table \"countries\". Then add fields (name, code). \"ID\" is added by default. And here write parameters in Extra value \"countries:id:name,countryCode:region=\"europe\"\".");
