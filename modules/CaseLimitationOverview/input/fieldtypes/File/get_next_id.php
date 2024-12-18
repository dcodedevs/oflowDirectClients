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
if(isset($v_response['status']) && 1 == $v_response['status'])
{
	$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
	$o_query = $o_main->db->query($s_sql);
	$fw_session = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
	
	$s_sql = "INSERT INTO uploads (created, createdBy) VALUES (NOW(), '".$o_main->db->escape_str($_COOKIE['username'])."')";
	$o_main->db->query($s_sql);
	$v_return['upload_id'] = $o_main->db->insert_id();
	$v_return['username'] = $_COOKIE['username'];
	$v_return['accountname'] = $o_main->accountinfo['accountname'];
	$v_return['token'] = $v_response['token'];
	$v_return['server_id'] = array_shift(explode('.', $_SERVER['HTTP_HOST']));//explode('.', $_SERVER['HTTP_HOST'])[0];
	$v_return['data'] = json_encode(array('action'=>'upload_file'));
	
	$v_token = array(
		'upload_id' => $v_return['upload_id'],
		'accountname' => $v_return['accountname'],
		'folder' => 'storage',
		'created' => date('Y-m-d H:i'),
	);
	$o_main->set_cdn_access_key($fw_session['content_server_access_key']);
	$v_return['cdn_token'] = urlencode($o_main->get_cdn_token($v_token));
	
	header('Content-Type: application/json');
	echo json_encode($v_return);
} else {
	throw new Exception('Error occurred handling request');
}