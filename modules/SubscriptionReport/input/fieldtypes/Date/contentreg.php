<?php
if(strpos($_POST[$fieldName],".") !== false)
{
	$split_value = explode(".",$_POST[$fieldName]);
	$_POST[$fieldName] = $split_value[2]."-".$split_value[1]."-".$split_value[0];
}
$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];   
?>