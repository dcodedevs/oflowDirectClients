<?php
$newModuleID = mysql_escape_string($_GET['newModuleID']);
$table = mysql_escape_string($_GET['table']);
$ID = mysql_escape_string($_GET['ID']);

//print "update {$table} set moduleID = '{$newModuleID}' where id = '{$ID}';";
mysql_query("update {$table} set moduleID = '{$newModuleID}' where id = '{$ID}';");

//header("Location: ".$_SERVER['HTTP_REFERER']);
header("Location: ".$_GET['return']);
?>
<h3><?php print $formText_ActionCompleted_Input; ?></h3>
<a href="<?php print $_GET['return']; ?>"><?php print $formText_GoBack_Input; ?></a>