<?php
$basemodule = $_GET['basemodule'];
$firstmodule = $_GET['firstmodule'];
$modulebase = $_GET['modulebase'];

$writeOut = "";
include("../../../$modulebase/input/settings/fields/".$basemodule."fields.php");  
foreach($prefields as $pre)
{
	$presplit = explode("¤",$pre);
	$writeOut .= ( $writeOut!="" ? ";" : "" ).$presplit[0].":".$presplit[0];
}	            

if($writeOut == "") $writeOut = "NONE";
echo $writeOut;
?>