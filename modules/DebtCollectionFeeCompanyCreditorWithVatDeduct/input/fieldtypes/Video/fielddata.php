<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field fieldtype used to either configure the youtube video to display on the webpage or upload the mp4 file to use in html5 video.

The data is saved in JSON format of this type of array:

array('videoItem', array('autoplay', 'related', 'controls', 'loop', 'muted', 'showinfo'), 'previewImage')

videoItem - can be either youtube link or array holding information of uploaded file. Array structure is the same as in File fieldtype
autoplay,related,controls,loop,muted,showinfo - 1 or 0 according to the checked checkboxes
previewImage - array holding information of uploaded Image. Array structure is the same as in Image fieldtype


use json_decode on this field to retreive all the inforation and fetch accordingly in output
");
?>
