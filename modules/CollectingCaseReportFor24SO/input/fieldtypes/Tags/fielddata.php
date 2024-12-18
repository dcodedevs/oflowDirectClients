<?php
$thisFieldType = 0;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'), 
"This field is for adding tags to content which could be organized in multi-level structure specifying parent tag.
Tags can be grouped in set, specifying setID (some number) in <b>Extra Field</b>.

Tags are stored in sys_tag and sys_tagcontent tables. Relation with content are stored in sys_tagrelation table.");
?>