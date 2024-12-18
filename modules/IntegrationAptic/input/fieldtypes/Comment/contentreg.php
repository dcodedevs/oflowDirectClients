<?php
//$fieldPos is same as $this->fieldNums[$f]
//$items = explode("::",$fields[$fieldPos][11]);
// load comments
$data = array();
$key1 = $fieldName."_date";
$key2 = $fieldName."_comment";
$key3 = $fieldName."_user";
if(array_key_exists($key1,$_POST) and array_key_exists($key2,$_POST) and array_key_exists($key3,$_POST))
{
	$_POST[$key2] = array_map("trim",$_POST[$key2]);
	foreach($_POST[$key2] as $i => $item)
	{
		if(strlen($item)==0) continue;
		
		if($_POST[$key1][$i]=="") $_POST[$key1][$i] = date("Y.m.d@H:i");
		$data[$i][] = $_POST[$key1][$i];
		$data[$i][] = htmlentities($item);
		$data[$i][] = $_POST[$key3][$i];
	}
}
//print_r($data);print json_encode($data);exit;
$fields[$fieldPos][6][$this->langfields[$a]] = str_replace(array("'","\r","\n"), array("\'",""," "), json_encode($data));
