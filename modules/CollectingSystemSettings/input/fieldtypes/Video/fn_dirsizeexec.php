<?php
// get dir size
function dirsizeexec($dir, $unit = 'b')
{
    //$dir = rtrim($dir, '/');
    if (!is_dir($dir)) {
        trigger_error("{$dir} not a folder/dir/path.", E_USER_WARNING);
        return false;
    }
    if (!function_exists('exec')) {
        trigger_error('The function exec() is not available.', E_USER_WARNING);
        return false;
    }
    $output = exec('du -sb ' . $dir);
    $filesize = (int) trim(str_replace($dir, '', $output));
    switch ($unit) {
        case 'g': $filesize = intval($filesize / 1073741824); break;  // giga
        case 'm': $filesize = intval($filesize / 1048576);    break;  // mega
        case 'k': $filesize = intval($filesize / 1024);       break;  // kilo
        case 'b': $filesize = intval($filesize);              break;  // byte
    }
    return ($filesize + 0);
}
?>