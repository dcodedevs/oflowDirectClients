<?php
$thisFieldname = "Menuconnection";
$thisFieldType = 12;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is for adding menuconnection to the module. Standard is to have one menu connection for each content. There is also a possibility to set it to save down a menuconnection for both level 1 and level 2 when selecting level 2. This is to be used in listings. If there is a wish to list in level 1 every content connected to either this level 1 or level 2 connected to this level 1. There is also possible to change fieldtype to allow multi connection. And then both as only last level or also here all level connection.

Write in Extra value following: [type],[connection_type],[menu_module_multi_select]

<b>Type:</b>
\t- S = One size select box. Allow only one choice.
\t- M(size of selectbox(optional)) = Multiplie selectbox with size as a following number. 5 is a default value.

<b>Connection type:</b>
\t- L = Only connect to last level
\t- A = Connect to all level.

<b>Menu module multi select:</b>
\t- MS = Multi select (possible to select menu points from multiple menu modules)

<strong>Example for standard</strong>: S,L.
<strong>Example for multi</strong>:M5,L or M5,A.
<strong>Example for multi menu select</strong>:M5,L,MS.");
?>