<?php
if(strlen($_POST[$fieldName]) >= 16) {
    $fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
} else {
    $fields[$fieldPos][6][$this->langfields[$a]]['error'] = 1;
}
?>
