<?php
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(__DIR__."/../includes/APIconnector.php");
require_once(__DIR__."/../includes/function.getModuleName.php");

if(isset($_COOKIE['username'], $_COOKIE['sessionID']))
{
	include(__DIR__.'/refresh_user_session.php');
}