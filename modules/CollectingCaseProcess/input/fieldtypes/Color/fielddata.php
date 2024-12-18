<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This is color picking field.
In extra value its possibe to specify following options:
	useAlpha: false;
	format: ['rgb','rgba','prgb','prgba','hex','hex3','hex6','hex8','hsl','hsla','hsv','hsva','name',boolean];
	useHashPrefix: false;
	fallbackColor: rgb(48, 90, 162);
	autoInputFallback: false;

Separate each option by :: and option key and value separate by :.
Example:
\"useAlpha:false::format:hex6::fallbackColor:#123123\".

More help <a target=\"_blank\" href=\"https://farbelous.github.io/bootstrap-colorpicker/tutorial-Basics.html\">here</a>");
?>