<?php
if(isset($_POST[$fieldName]) and $_POST[$fieldName] == 1)
{
	$fields[$fieldPos][6][$this->langfields[$a]] = 1;
} else {
	$fields[$fieldPos][6][$this->langfields[$a]] = 0;
}
