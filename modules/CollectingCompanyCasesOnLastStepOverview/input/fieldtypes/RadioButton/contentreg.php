<?php
if(isset($_POST[$fieldName]))
{
	$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
} else {
	$fields[$fieldPos][6][$this->langfields[$a]] = -1;
}
