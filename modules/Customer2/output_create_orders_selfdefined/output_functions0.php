<?php
/**
 * Variable cleaning - SQL security
 */
function sql_esc($str)
{
    $str = trim($str);
	if(get_magic_quotes_gpc()) $str = stripslashes($str);
    return mysql_real_escape_string($str);
}

/**
 *
 * TODO: add other widely used functions:
 * checkValue
 * $db->...
 *
 * TODO: implement helpers
 * protectedFileLink('directory/file') - get protected file link
 * accountPath, modulePath
 */
?>
