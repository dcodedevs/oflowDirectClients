<?php
$thisFieldname = "Menuconnection";
$thisFieldType = 12;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is used to create multiple menuconnections. Mostly used for the products module. 

in <b>Extra Field</b> can be specified which menu modules you want to show in such manner

[module_name],[module_name],[module_name],...

Example: ProductsMenu

Example: ProductsMenu, TopMenu
");
