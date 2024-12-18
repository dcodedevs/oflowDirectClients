<?php
$v_replace = array("/", ".", "\\");
$buttonType = str_replace($v_replace,"",$_GET['buttontype']);
$executefile = str_replace($v_replace,"",$_GET['executefile']); 

$findDir = $extradir."/input/buttontypes/$buttonType/$executefile.php";
include($findDir);
?>