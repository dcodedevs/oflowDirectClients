<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(BASEPATH.'modules/Languages/input/includes/APIconnect.php');

if(!$o_main->db->table_exists('uploads'))
{
	$o_main->db->simple_query("CREATE TABLE `uploads` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`moduleID` INT(11) NULL DEFAULT NULL,
		`createdBy` CHAR(255) NULL DEFAULT NULL,
		`created` DATETIME NULL DEFAULT NULL,
		`updatedBy` CHAR(255) NULL DEFAULT NULL,
		`updated` DATETIME NULL DEFAULT NULL,
		`origId` INT(11) NULL DEFAULT NULL,
		`sortnr` INT(11) NULL DEFAULT NULL,
		`filename` TEXT NULL,
		`filepath` TEXT NULL,
		`size` INT(11) NULL DEFAULT NULL,
		PRIMARY KEY (`id`),
		INDEX `origIdIdx` (`origId`)
	)");
}

$s_response = APIconnectAccount("account_authenticate", $o_main->accountinfo['accountname'], $o_main->accountinfo['password'], array('VALID_COUNT'=>20));
$v_response = json_decode($s_response, TRUE);

$s_sql = "INSERT INTO uploads (created, createdBy) VALUES (NOW(), '".$o_main->db->escape_str($_COOKIE['username'])."')";
$o_main->db->query($s_sql);
$v_return['upload_id'] = $o_main->db->insert_id();
$v_return['username'] = $_COOKIE['username'];
$v_return['accountname'] = $o_main->accountinfo['accountname'];
$v_return['token'] = $v_response['token'];
$v_return['server_id'] = array_shift(explode('.', $_SERVER['HTTP_HOST']));//explode('.', $_SERVER['HTTP_HOST'])[0];
$v_return['data'] = json_encode(array('action'=>'upload_image'));

header('Content-Type: application/json');
echo json_encode($v_return);