<?php
$subfolder = $_GET['subfolder'];
if($subfolder != "languagesOutput"){
    include(__DIR__."/".$subfolder."/output.php");
}
?>
