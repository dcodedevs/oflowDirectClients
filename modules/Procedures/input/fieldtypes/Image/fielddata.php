<?php
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisFieldType = 22;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array("<br />","&nbsp;&nbsp;"),
"This field is for one or more images with or without text in different languages.

Images are resized based on resize parameters which are defined like this <b>\"[width_1],[height_1],[resize_option (optional)],[other_option (optional)]\"</b>.

In <b>Extra Value</b> write parameters in such manner:
<b>[type],[limit]:[w1],[h1]:[w2],[h2]</b> and so on for resize.

<b>Types are:</b>
\t\t<b>T1</b> - Image without text;
\t\t<b>T2</b> - Image with text;
\t\t<b>T2S</b> - Image with single text (forced);
\t\t<b>Link</b> - (addon) Append \"Link\" to each previous type to add link field for image;
\t\t<b>P</b> - (addon) Append \"P\" to protect all size images. Individual size image can be protected also, see below;
\t\t<b>O</b> - (addon) Append \"O\" to leave original image in storage. By default original files are removed from storage after content save.
\t\t<b>Q</b> - (addon) Append \"Q\" to use 100% quality of original image. By default image quality gets reduced to 75%.

<b>Limit</b> (Optional) - after comma specify image limit, example: T1,4:100,100. Default limit value is 1.

<b>Example:</b><br>\"T2:100,150:300,400\", \"T1\" or \"T1,10:100,100\".

In <b>Resize option</b> you can specify (optional):
\t\t\"<b>C</b>\" - current image will be cropped to defined size;
\t\t\"<b>AC</b>\" - current image will be automatically cropped in center to defined size;
\t\t\"<b>M</b>\" - current image will be resized to minimal image side. Use for thumbs when you need resize to at least 125px, then write \"125,125,M\".

In <b>Other option</b> you can specify (optional):
\t\t\"<b>P</b>\" - current size image will be moved to protected folder;
\t\t\"<b>F</b>\" - current size image will have focus point.

It is possible to store images in protected folder by adding <b>P</b> symbol in <b>type</b> or <b>other_option</b>.

Example, \"T1P:100,100\", \"T1:100,100,C:0,0,,P\" or \"T1:100,100,C,F:500,500,AC,F:0,0,,PF\"");
?>