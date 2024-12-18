<?php
if(isset($_POST[$fields[$this->fieldNums[$f]][1]]))
{
	$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
} else {
	$fields[$fieldPos][6][$this->langfields[$a]] = $level;
}
if($fields[$fieldPos][6][$this->langfields[$a]] > 0 && intval($_POST['menulevelparentlevelID']) < 1)
{
	$fields[$fieldPos][6][$this->langfields[$a]] = 0;
}
?>