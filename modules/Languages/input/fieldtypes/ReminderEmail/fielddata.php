<?php
$thisFieldType = 0;
$thisDatabaseField = "TEXT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array("<br />","&nbsp;&nbsp;"),
"This field is for creating e-mail reminders for current content on appropriate time in future or right now.

In Extra value there should be set name of email template which is located in module [current_module]/<b>output_ReminderEmailFieldtype</b>/ folder. If Extra value is empty, then <b>standard</b> template is used which also should be in output_ReminderEmailFieldtype folder.

In template should be defined <b>\$emailSubjectData</b> and <b>\$emailBodyData</b> values.

For SQL in template use following variables:
\t- <b>\$templateTable</b>
\t- <b>\$templateID</b>
\t- <b>\$templateLanguageID</b>

After saving content there will be displayed reminder report - for that purpouse there should be added module <b>\"Reminder\"</b>.

To use Member system in module table settings should be defined sendEmail sources.");
?>