<?php

// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit init.php location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);
$v_tmp = explode("/",realpath(__DIR__.'/../'));
$_POST['folder'] = array_pop($v_tmp);

// Load database
require_once BASEPATH . 'elementsGlobal/cMain.php';

// Load API
require_once BASEPATH . 'fw/account_fw/includes/APIconnector.php';
require_once BASEPATH . 'fw/account_fw/includes/class.variables.php';

$o_query = $o_main->db->get('accountinfo');
$v_accountinfo = $accountinfo = $o_query ? $o_query->row_array() : array();

// Check session from cookie
$username = $sessionID = '';
if(isset($_COOKIE['username']) || isset($_POST['cookie_username']))
{
	$cookie_username = isset($_POST['cookie_username']) ? $_POST['cookie_username'] : $_COOKIE['username'];
	$cookie_sessionID = isset($_POST['cookie_sessionID']) ? $_POST['cookie_sessionID'] : $_COOKIE['sessionID'];

    //Check username before loading page
	$response = json_decode(APIconnectorUser("usersessionget", $cookie_username, $cookie_sessionID));

	if(!array_key_exists("error",$response))
	{
		$sessionID = $_SESSION['sessionID'] = $cookie_sessionID;
		$username = $_SESSION['username'] = $response->data->userName;
		$languageID = $response->data->languageID;
		$userCountry = $response->data->country;
		$userID = $response->data->userID;
		$companyID = $_GET['companyID'];
		
		$param = array(
			'languageID' => $languageID,
			'userCountry' => $userCountry,
			'pageID' => 0,
			'loggID' => $username,
			'logget' => 0,
			'sessionID' => $sessionID,
			'userID' => $userID
		);
		$variables = new Variables($param);
		
		$o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($variables->sessionID)."' AND username = '".$o_main->db->escape_str($variables->loggID)."' LIMIT 1");
		
		if($o_query && $o_query->num_rows()>0)
		{
			$fw_session = $o_query->row_array();
			$variables->fw_session = $fw_session;
			$variables->menu_access = json_decode($fw_session['cache_menu'], TRUE);
			$variables->developeraccess = $fw_session['developeraccess'];
			$variables->companyname = $fw_session['companyname'];
			$variables->accountnamefriendly = $fw_session['accountnamefriendly'];
			$variables->useradmin = $fw_session['useradmin'];
			$variables->system_admin = $fw_session['system_admin'];
			$variables->userConnectedToId = $fw_session['userConnectedToId'];
			$variables->membersystemmodule = $fw_session['membersystemmodule'];
			$variables->companyaccessID = $fw_session['companyaccessID'];
			$variables->style_set = $fw_session['style_set'];
			$variables->global_style_set = $fw_session['global_style_set'];
			$variables->invitation_config = $fw_session['invitation_config'];
			// Get framework settings
			$o_query = $o_main->db->get('frameworksettings_basisconfig');
			$variables->fw_settings_basisconfig = $o_query ? $o_query->row_array() : array();
			$o_query = $o_main->db->query("SELECT *, IF(id = '".$o_main->db->escape_str($variables->style_set)."', 0, 1) AS priority FROM frameworksettings WHERE id = '".$o_main->db->escape_str($variables->style_set)."' OR id > 0 ORDER BY priority, id LIMIT 1");
			$variables->fw_settings_accountconfig = $o_query ? $o_query->row_array() : array();
			// Account info
			$variables->accountinfo = $v_accountinfo;
			
			//account_id stored in accountinfo
			if($v_accountinfo['getynet_account_id']){
				$variables->account_id = $v_accountinfo['getynet_account_id'];
			}
		}
	}
} else {
	unset($_SESSION['sessionID']);
	unset($_SESSION['username']);
}

// Get account base path
$request_uri = explode('/',$_SERVER['REQUEST_URI']);
$base_url = 'https://' . $_SERVER['SERVER_NAME'] . '/'. $request_uri[1] . '/' . $request_uri[2];
define('ACCOUNT_BASE_URL', $base_url);
define('ACCOUNT_FW_URL', $base_url . '/fw/index.php');