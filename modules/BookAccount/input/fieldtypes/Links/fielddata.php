<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is account url's dropdown. Where list of modules and module url can be selected for an element:
<b>[s/e],[1/2]:[moduleID],[moduleID],[moduleID]:[a]</b> and so on.
<b>s</b> - show only choosen modules
<b>e</b> - exclude choosen modules
<b>1</b> - for enabling only external URL field
<b>2</b> - for showing only modules links
<b>3</b> - for showing boths external URL field and modules links
<b>a</b> - possibility to add anchor

For example, <b>\"e,1:30,33:a\"</b> or <b>\"::a\"</b> for anchor only");
?>