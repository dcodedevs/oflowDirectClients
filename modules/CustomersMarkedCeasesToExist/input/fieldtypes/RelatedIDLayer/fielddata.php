<?php
$thisFieldType = 0;
$thisDatabaseField = "CHAR(50)";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'), 
"This field is popup layer list of values from other module.
Link parameters to other module (table) should be written in <b>Extra Field</b> in such manner

[table_name](::)[id_parameters](::)[field_parameters](::)[items_per_page], where

\t- [<strong>table_name</strong>] is related table name,
\t- [<strong>id_parameters</strong>] consists of \"[related_table_identification]:[label]:[optional current_table_filter_field]:[optional related_table_filter_field]\"
\t- [<strong>field_parameters</strong>] consist of such structure [field_type]:[field name in table]:[label]:[optional current table field name which should be changed]:[optional field_extra] which could be repeated more times separated by comma. 
\t\t- [<strong>field_type</strong>] can contain such symbols:
\t\t\t <strong>v</strong> - visible in edit page
\t\t\t <strong>d</strong> - displayed in popup layer
\t\t\t <strong>c</strong> - field changes current table field
\t\t\t <strong>r</strong> - gets related table related data, like showing product list and get product brand. Field_extra shuld be defined: [table]#[filter_id]#[field_to_get]
\t\t\t <strong>i</strong> - copy FileOrImage fieldtype. Field_extra shuld be defined: [image_resize_index]#[width]#[height]#[C,AC,M - same as for FileOrImage]. More resize indexes can be used, separated with (#).
\t- [<strong>items_per_page</strong>] is count of items to be shown in page (optional). Default is 20 items per page.

For example, if you want to list countries in list, then create module Countries and table \"countries\". Then add fields (name, code). \"ID\" is added by default. And here write parameters in Extra field \"countries(::)id:ID (::)vd:name:Name:,c:code::billingCode(::)10\".

In this case for current field is assigned value id of countries table, displayed name after field and in popup list. And in same page <b>billingCode</b> field is changed by countries.code value.

After input change there is trigerred JS function <b>changed_[field_ui_id]([changed_id])</b>, so such <b>function must be in field JS textarea</b>.

Example: function changed_[:replace:products_country](id) {}

[:replace:products_country] will be replaced with correct field_ui_id.");
?>