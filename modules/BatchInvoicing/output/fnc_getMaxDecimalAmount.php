<?php
if(!function_exists("getMaxDecimalAmount")) {
    function getMaxDecimalAmount($number){
        if ((int)$number == $number)
        {
            return 0;
        }
        else if (! is_numeric($number))
        {
            // throw new Exception('numberOfDecimals: ' . $value . ' is not a number!');
            return false;
        }
        $number = $number+0;
        return strlen($number) - strrpos($number, '.') - 1;
    }
}
?>
