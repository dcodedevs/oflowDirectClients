<?php
ob_start();


// Turn on/off debug mode
define('SYS_DEBUG', FALSE);

// DB connection
define("BASEPATH", realpath(__DIR__."/../").DIRECTORY_SEPARATOR);
require_once(BASEPATH."elementsGlobal/cMain.php");

// Api connect
if(!function_exists("APIconnectAccount")) include(__DIR__."/../modules/Languages/input/includes/APIconnect.php");

// Api connector - used in handle_fw_session.php to get response without error printout that breaks JSON response
if(!function_exists("APIconnectorUser")) include(__DIR__."/../fw/account_fw/includes/APIconnector.php");

require_once(__DIR__."/../fw/account_fw/includes/fn_fw_api_call.php");

// This header setup is required by different http request
// libs that require allow origin headers for example axios
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

// If request was made with application/json
$v_data = json_decode(file_get_contents('php://input'), true);

// Fallback to x-www-form-urlencoded request
// We assume that this request has json_encoded "data" param
// containing all data
if (!$v_data) {
    $v_data = json_decode($_POST['data'], true);
}

// Handle debugging
if (SYS_DEBUG) {
    // Create table
	$o_main->db->simple_query("CREATE TABLE `sys_debug` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`get` TEXT NULL,
		`post` TEXT NULL,
		`input` LONGTEXT NULL,
		`files` LONGTEXT NULL,
		`cookie` LONGTEXT NULL,
		`output` LONGTEXT NULL,
		`url` TEXT NULL,
		`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
	)");

    // Build query
	$s_sql = "INSERT INTO sys_debug SET `created` = NOW(), `url` = '".$o_main->db->escape_str($_SERVER['PHP_SELF'])."', `get` = '".$o_main->db->escape_str($_SERVER['QUERY_STRING'])."', `post` = '".$o_main->db->escape_str(json_encode($v_data))."', `input` = '".$o_main->db->escape_str(file_get_contents('php://input'))."', `files` = '".$o_main->db->escape_str(json_encode($_FILES))."', `cookie` = '".$o_main->db->escape_str(json_encode($_COOKIE))."'";

    // Execute
    $o_main->db->query($s_sql);

    // Get debug entry id
    $l_system_debug_id = $o_main->db->insert_id();
}

// Return array with default status code - 0
$v_return = array(
    'status' => 0,
    'public' => false
);

/**
 * There are three ways how to authenticate API calls to get access to protected API endpoints:
 * 1) with valid access token that is stored in sys_api_access table (SysApiAccess module)
 * 2) with account access token (tmp token, created with APIconnectAccount account_access_token method)
 * 3) with valid username and sessionID
 *
 * YOU SHOULD NOT use them more then one at the same time.
 *
 * NOTE: API endpoints stored in public folder is accessible without any of tokens
 */

// Access check
// Valid access token must at least 16 characters long
$access_check = false;
$access_token = $v_data['access_token'];
if ($access_token) {
    $access_sql = "SELECT * FROM sys_api_access
    WHERE access_token = ? AND (expiration_date >= CURDATE() OR expiration_date IS NULL OR expiration_date = '0000-00-00')";
    $o_query = $o_main->db->query($access_sql, array($access_token));
    if($o_query && $o_query->num_rows()>0 && strlen($access_token) >= 16)
    {
    	$access_check = true;
    }
}

// User access check
$user_access_check = FALSE;
if ($v_data['username'] && $v_data['sessionID'])
{
	include(__DIR__.'/include/handle_fw_session.php');
	if($b_session_created && $v_fw_session['session'] && $v_connect_status == 1)
	{
		$user_access_check = TRUE;

		// User data
		$fw_api_user_data = json_decode($v_fw_session['user_profile'], TRUE);
		$fw_api_user_data['userID'] = $fw_api_user_data['id'];
	}
}


// Account access token
//
// More info:
// APIconnectAccount
// account_authenticate
// account_authenticate_check
$account_access_token = $v_data['account_access_token'];
$account_access_caller_account_name = $v_data['account_access_caller_account_name'];
if ($account_access_token && $account_access_caller_account_name) {

    $o_query = $o_main->db->get('accountinfo');
    $accountinfo_data = $o_query ? $o_query->row_array() : array();
    $account_name = $accountinfo_data['accountname'];
    $account_password = $accountinfo_data['password'];

    $response = APIconnectAccount("account_authenticate_check", $account_name, $account_password, array(
        'ACC_NAME'=> $account_access_caller_account_name,
        'TOKEN'=> $account_access_token
    ));

    $response_decoded = json_decode($response, true);
    $status = $response_decoded['status'];

    if ($status == 1) {
        $account_acces_check = true;
    }
}

// We have some request data
if($v_data) {
    // Action is specified in params
	if(isset($v_data['action'])) {
		// Action and module name
		$s_action = preg_replace('#[^A-za-z0-9_]+#', '', $v_data['action']);
		$s_module = preg_replace('#[^A-za-z0-9_]+#', '', $v_data['module']);

		// Dir
		$dir = $s_module ? __DIR__ . '/../modules/' . $s_module . '/api' : __DIR__. '/actions';

		// Public and protected action file paths
		$publicActionFile = $dir . '/public/' . $s_action . '.php';
		$protectedActionFile = $dir . '/protected/' . $s_action . '.php';

		// Check access
		// If request has valid access token first try to load protected action
		// and if that fails fallback to public
		if ($access_check || $account_acces_check || $user_access_check) {
			if(is_file($protectedActionFile)) require_once $protectedActionFile;
			elseif(is_file($publicActionFile)) require_once $publicActionFile;
			else $v_return['message'] = "Incorrect action";
		}
		// If no valid access token is present, try to load public action
		else {
			if(is_file($publicActionFile)) require_once $publicActionFile;
			else $v_return['message'] = "No access for this action (or public action missing)";
		}
	}
	// No action specified in params
	else {
		$v_return['message'] = "Missing action";
	}
}
// Missing params
else {
	$v_return['message'] = "Missing data";
}
// JSON RETURN
print json_encode($v_return);

// Final debug update
if(SYS_DEBUG) {
	$o_main->db->query("UPDATE sys_debug SET output = ? WHERE id = ?", array(ob_get_contents(), $l_system_debug_id));
}
$o_main->db->close();
