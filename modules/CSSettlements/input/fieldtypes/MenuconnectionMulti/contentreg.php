<?php
$l_menulevel_id = $_POST[$fieldName.'main_connected'];
if(strpos($l_menulevel_id,"_") !== FALSE)
{
	$l_menulevel_id = explode("_", $l_menulevel_id);
	$l_menulevel_id = $l_menulevel_id[1];
}
$fields[$fieldPos][6][$this->langfields[$a]] = $l_menulevel_id;
