<?php
$x = 10;
define('BASEPATH', __DIR__."/../../..".DIRECTORY_SEPARATOR);
include("config.php");
include_once("class_procedure_create_invoices.php");
require_once(__DIR__.'/../../../elementsGlobal/cMain.php');
$execute = $indexes = $cmd = array();
$helper = new procedure_create_invoices();

$lines = explode("\n",$proc_config);
foreach($lines as $line)
{
	$line = trim($line);
	if($line == "") continue;
	$command = explode(" ",$line,2);
	$command[0] = strtoupper($command[0]);
	if($command[0] == "RUN")
	{
		$helper->multi_dim_array_set_value($execute, $indexes, $x, array($command[0],$command[1]));
	}
	else if($command[0] == "EACHLINE")
	{
		$helper->multi_dim_array_set_value($execute, $indexes, $x, array($command[0],$command[1],"child"=>array()));
		$indexes[] = $x;
		$indexes[] = "child";
	}
	else if($command[0] == "IF")
	{
		$helper->multi_dim_array_set_value($execute, $indexes, $x, array($command[0],$command[1],"child"=>array()));
		$indexes[] = $x;
		$indexes[] = "child";
	}
	else if($command[0] == '{')
	{}
	else if($command[0] == '}')
	{
		array_pop($indexes);
		array_pop($indexes);
	}
	$x++;
}

//print_r($execute);
$moduleID = 0;
$procrunresulttext = "";
//if(!isset($v_proc_variables)) $v_proc_variables = array();
$helper->run_procedure($execute, $moduleID, $procrunresulttext, $v_proc_variables, 0, 0, $o_main);
?>
