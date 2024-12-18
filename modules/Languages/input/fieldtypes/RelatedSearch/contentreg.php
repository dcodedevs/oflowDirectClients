<?php
$options = explode(":",$fields[$fieldPos][11]);
if(trim($options[6]) == 1)
{
	$fields[$fieldPos][6][$this->langfields[$a]] = json_encode($_POST[$fieldName]);
}
?>