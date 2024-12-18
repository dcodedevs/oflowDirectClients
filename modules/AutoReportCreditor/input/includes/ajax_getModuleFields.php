<?php
$basemodule = preg_replace('#[^A-za-z0-9_]+#', '', $_GET['basemodule']);
$modulebase = preg_replace('#[^A-za-z0-9_]+#', '', $_GET['modulebase']);

$writeOut = "";
$s_file = __DIR__.'../../../'.$modulebase.'/input/settings/fields/'.$basemodule.'fields.php';
if(is_file($s_file))
{
	include($s_file);  
	foreach($prefields as $pre)
	{
		$presplit = explode("¤",$pre);
		$writeOut .= ( $writeOut!="" ? ";" : "" ).$presplit[0].":".$presplit[0];
	}	
}

if($writeOut == "") $writeOut = "NONE";
echo $writeOut;