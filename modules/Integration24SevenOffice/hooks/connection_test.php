<?php
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../elementsGlobal/cMain.php';
global $o_main;
// Load integration
require_once __DIR__ . '/../internal_api/load.php';
$api = new Integration24SevenOffice(array(
    'o_main' => $o_main,
    'ownercompany_id' => 1
));
?>
