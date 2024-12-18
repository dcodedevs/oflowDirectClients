<?php
$thisDatabaseField = "LONGTEXT";
$thisShowOnList = 1;
$thisFieldType = 22;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array("<br />","&nbsp;&nbsp;"),
"This field is for one or more file upload with or without text in different languages.

In <b>Extra Value</b> write parameters in such manner:
<b>[type],[limit]</b>.

<b>Types are:</b>
\t\t<b>T1</b> - File without text;
\t\t<b>T2</b> - File with text;
\t\t<b>T2S</b> - File with single text (forced);
\t\t<b>Link</b> - (addon) Append \"Link\" to each previous type to add link for file;
\t\t<b>P</b> - (addon) Append \"P\" to protect file.
\t\t<b>O</b> - (addon) Append \"O\" to leave original file in storage. By default original files are removed from storage after content save.

<b>Limit</b> (Optional) - after comma specify file limit, example: T1,4. Default limit value is 1.

<b>Example:</b><br>\"T2\", \"T1\" or \"T1,10\".");
?>