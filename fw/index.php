<?php
if($_SERVER['REMOTE_ADDR']=='87.110.235.137') {
	register_shutdown_function('catch_fatal_error');
}
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
/*$s_log_id = uniqid();
$l_log_start = microtime(true);
$l_log_number = floor($l_log_start);
$l_log_fraction = round($l_log_start - $l_log_number, 4);
$s_log_time = date("Y-m-d H:i:s", $l_log_start).' '.$l_log_fraction;
file_put_contents(__DIR__.'/../uploads/0_log.log', $s_log_time.' START('.$s_log_id.'): '.json_encode($_REQUEST).PHP_EOL, FILE_APPEND);*/
ob_start();
session_start();
header('Content-Type: text/html; charset=utf-8');

define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../'));
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
define('FW_AJAX', (isset($_POST['fwajax']) && 1 == $_POST['fwajax']));
define('FW_AJAX_LESS', ((isset($_POST['fwajax_less']) && 1 == $_POST['fwajax_less']) || (in_array($_GET['module'], array('CollectingCases', 'CollectingCompanyCases')) && $_GET['inc_act'] === 'getTabNumbers')));
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);  

require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(__DIR__."/account_fw/includes/class.phpmailer.php");
require_once(__DIR__."/account_fw/includes/class.variables.php");
require_once(__DIR__."/account_fw/includes/APIconnector.php");
require_once(__DIR__."/account_fw/includes/function.getModuleName.php");
require_once(__DIR__."/account_fw/includes/function.updateUrlQuery.php");
require_once(__DIR__."/account_fw/includes/function.fwCurrentPageUrl.php");
require_once(__DIR__."/account_fw/includes/fn_account_root_url.php");
require_once(__DIR__."/account_fw/includes/fn_log_action.php");
require_once(__DIR__."/account_fw/includes/fn_fw_api_call.php");
require_once(__DIR__."/account_fw/includes/fn_log_content_activity.php");

if(FRAMEWORK_DEBUG)
{
	if(!$o_main->db->table_exists('sys_debug'))
	{
		$b_activate_log = $o_main->db->simple_query('CREATE TABLE sys_debug (
			id INT(11) NOT NULL AUTO_INCREMENT,
			get TEXT NULL,
			post TEXT NULL,
			cookie TEXT NULL,
			output MEDIUMTEXT NULL,
			url TEXT NULL,
			created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		)');
	}
	$v_insert = array(
		'created'=>date("N", time()),
		'url'=>$_SERVER['PHP_SELF'],
		'get'=>$_SERVER['QUERY_STRING'],
		'post'=>json_encode($_POST),
		'cookie'=>json_encode($_COOKIE)
	);
	$o_main->db->insert('sys_debug', $v_insert);
	$l_system_debug_id = $o_main->db->insert_id();
}

if(isset($_GET['logout']))
{
	$host = strtolower($_SERVER['HTTP_HOST']);
	if(strpos($host,"www")!==false) $host = substr($host,4);
	log_action("logout");
	$response = json_decode(APIconnectorUser("userlogout", $_COOKIE['username'], $_COOKIE['sessionID']));
	setcookie("username", false, - 3600, '', ".$host");
	setcookie("password", false, - 3600, '', ".$host");

	if($response == "OK")
	{
		session_regenerate_id();
		header("Location: https://www.getynet.com");
	}
} else {
	$o_query = $o_main->db->get('accountinfo');
	$o_main->accountinfo = $v_accountinfo = $accountinfo = $o_query ? $o_query->row_array() : array();
	$v_accountinfo_basisconfig = array();
	if($o_main->db->table_exists('accountinfo_basisconfig'))
	{
		$o_query = $o_main->db->get('accountinfo_basisconfig');
		$v_accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
	}
	$o_main->accountinfo_basisconfig = $v_accountinfo_basisconfig;
	$o_main->accountinfo['activate_2factor_authentification'] = (int)$o_main->accountinfo['activate_2factor_authentification'];
	include(__DIR__.'/index_main.php');
}
if(FRAMEWORK_DEBUG)
{
	$o_main->db->update('sys_debug', array('output'=>ob_get_contents()), array('id'=>$l_system_debug_id));
}
$o_main->db->close();

/*$l_log_end = microtime(true);
$l_log_number = floor($l_log_end);
$l_log_fraction = round($l_log_end - $l_log_number, 4);
$s_log_time = date("Y-m-d H:i:s", $l_log_end).' '.$l_log_fraction;
file_put_contents(__DIR__.'/../uploads/0_log.log', $s_log_time.' END('.$s_log_id.'): '.($l_log_end - $l_log_start).PHP_EOL.PHP_EOL, FILE_APPEND);*/

if(1==0&&$_SERVER['REMOTE_ADDR']=='87.110.235.137') {
	print_r($o_main->db->queries);
	print_r($o_main->db->query_times);
}
function catch_fatal_error()
{
	// Getting Last Error
	$last_error =  error_get_last();

	// Check if Last error is of type FATAL
	if(isset($last_error['type']) && in_array($last_error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR)))
	{
		// Fatal Error Occurs
		$s_message = "Fatal error occurred executing script:\n".__FILE__."\n\nError details:\n".var_export($last_error, TRUE);
		//ob_end_clean();
		die($s_message);
	}
}