<?php
//-------------------------------------------------------------------
// Mobile webview & cookie handling workaround
//-------------------------------------------------------------------
$b_fw_getynet_app = (isset($_COOKIE['fw_mobile_output']) && $_COOKIE['fw_mobile_output'] == 1);

if ($_SERVER['HTTP_MOBILE_WEBVIEW_USERNAME'] && $_SERVER['HTTP_MOBILE_WEBVIEW_SESSIONID'] && $_SERVER['HTTP_MOBILE_WEBVIEW_CAID']) {
	$_COOKIE['username'] = $_SERVER['HTTP_MOBILE_WEBVIEW_USERNAME'];
    $_COOKIE['sessionID'] = $_SERVER['HTTP_MOBILE_WEBVIEW_SESSIONID'];
    $_COOKIE[$accountname . '_caID'] = $_SERVER['HTTP_MOBILE_WEBVIEW_CAID'];
	$_COOKIE['fw_mobile_output'] = 1;

	setcookie("username", $_SERVER['HTTP_MOBILE_WEBVIEW_USERNAME'], time()+60*60*24);
    setcookie("sessionID", $_SERVER['HTTP_MOBILE_WEBVIEW_SESSIONID'], time()+60*60*24);
    setcookie($accountname . "_caID", $_SERVER['HTTP_MOBILE_WEBVIEW_CAID'], time()+60*60*24);
    setcookie("fw_mobile_output", 1, time()+60*60*24);

	$b_fw_getynet_app = TRUE;
}

if($_GET['pageID'] == 100) {
	$b_fw_getynet_app = true;
}
//-------------------------------------------------------------------

if($_COOKIE['username'] == '')
{
	log_action('fail', 'empty_username');
	session_regenerate_id();
	header('Location: https://www.getynet.com/');
	return;
}
else if($_COOKIE['username'] != '' && $_COOKIE['sessionID'] && !isset($_GET['pageID']) && isset($_GET['cID']))
{
	if(isset($_SESSION['fw_screen_width']) && isset($_SESSION['fw_screen_height'])){
	} else if(isset($_REQUEST['width']) && isset($_REQUEST['height'])) {
		$_SESSION['fw_screen_width'] = $_REQUEST['width'];
		$_SESSION['fw_screen_height'] = $_REQUEST['height'];
		header('Location: ' . $_SERVER['PHP_SELF']);
	} else {
		echo '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF']. '?' . $_SERVER['QUERY_STRING'] . '&width="+screen.width+"&height="+screen.height;</script>';
		return;
	}

	if(isset($_GET['companyID']))
	{
		$companyID = $_GET['companyID'];
	} else {
		list($companyID,$tmp) = split('_',$_GET['company_account']);
	}

	if(!$o_main->db->table_exists('session_framework'))
	{
		$b_table_created = $o_main->db->query("CREATE TABLE session_framework (
			companyaccessID CHAR(64) NOT NULL,
			session CHAR(50) NOT NULL,
			username CHAR(100) NOT NULL,
			IP CHAR(32) NOT NULL,
			accountlanguageID CHAR(10) NOT NULL,
			developerlanguageID CHAR(15) NOT NULL,
			cache_menu TEXT NOT NULL,
			cache_userstatus TEXT NOT NULL,
			cache_messageshowto TEXT NOT NULL,
			cache_contactset TEXT NOT NULL,
			cache_userlist LONGTEXT NOT NULL,
			cache_right TEXT NOT NULL,
			cache_access_level TEXT NOT NULL,
			cache_timestamp DATETIME NOT NULL,
			accountnamefriendly CHAR(255) NOT NULL,
			upload_quota TEXT NOT NULL,
			companyname CHAR(255) NOT NULL,
			useradmin TINYINT(4) NOT NULL,
			system_admin TINYINT(4) NOT NULL,
			accesslevel TEXT NOT NULL,
			developeraccess TINYINT(4) NOT NULL DEFAULT '0',
			developeraccessoriginal TINYINT(4) NOT NULL DEFAULT '0',
			groupID TEXT NOT NULL,
			groupname TEXT NOT NULL,
			user_groups TEXT NOT NULL DEFAULT '',
			userConnectedToId INT(11) NOT NULL,
			membersystemmodule CHAR(255) NOT NULL,
			departmentenable TINYINT(4) NOT NULL,
			fullname CHAR(50) NOT NULL,
			profileimage CHAR(255) NOT NULL,
			fwbaseurl TEXT NOT NULL,
			error_msg TEXT NOT NULL,
			variables TEXT NOT NULL,
			urlpath TEXT NOT NULL,
			returl TEXT NOT NULL,
			channel_creation_level_for_all_users TINYINT(4) NOT NULL DEFAULT '0',
			account_edition INT(11) NOT NULL DEFAULT '0',
			user_profile TEXT NOT NULL DEFAULT '',
			style_set TEXT NOT NULL DEFAULT '',
			global_style_set TEXT NOT NULL DEFAULT '',
			invitation_config TEXT NOT NULL DEFAULT '',
			frontpage_config TEXT NOT NULL DEFAULT '',
			content_server_api_url TEXT NOT NULL DEFAULT '',
			last_request_time DATETIME NULL DEFAULT NULL,
			connection_type TINYINT(4) NOT NULL DEFAULT '0',
			app_name TEXT NOT NULL DEFAULT '',
			app_version TEXT NOT NULL DEFAULT '',
			app_platform TEXT NOT NULL DEFAULT '',
			device_name TEXT NOT NULL DEFAULT '',
			device_year_class TEXT NOT NULL DEFAULT '',
			sdk_version TEXT NOT NULL DEFAULT '',
			browser_version TEXT NOT NULL DEFAULT '',
			screen_resolution TEXT NOT NULL DEFAULT '',
			membership_tags TEXT NOT NULL DEFAULT '',
			active_crm_subscription TINYINT(4) NOT NULL DEFAULT '0',
			expired TINYINT(4) NOT NULL DEFAULT '0',
			INDEX Idx_1 (companyaccessID),
			INDEX Idx_2 (session, username, IP)
		)");
		if(!$b_table_created)
		{
			echo 'error_occured_on_account_install'.'<br>';
			if(FRAMEWORK_DEBUG) print_r($o_main->db->error());
			log_action('error_occured_on_account_install');
			return;
		}
	}
	if(!$o_main->db->field_exists('system_admin', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN system_admin TINYINT(4) NOT NULL DEFAULT '0' AFTER useradmin;");
	}
	if(!$o_main->db->field_exists('channel_creation_level_for_all_users', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN channel_creation_level_for_all_users TINYINT(4) NOT NULL DEFAULT '0' AFTER returl;");
	}
	if(!$o_main->db->field_exists('account_edition', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN account_edition INT(11) NOT NULL DEFAULT '0';");
	}
	if(!$o_main->db->field_exists('user_groups', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN user_groups TEXT NOT NULL DEFAULT '' AFTER groupname;");
	}
	if(!$o_main->db->field_exists('force_cache_refresh', 'accountinfo'))
	{
		$o_main->db->query("ALTER TABLE accountinfo ADD COLUMN force_cache_refresh DATETIME NULL DEFAULT NULL;");
	}
	if(!$o_main->db->field_exists('user_profile', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN user_profile TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('style_set', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN style_set TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('global_style_set', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN global_style_set TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('invitation_config', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN invitation_config TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('frontpage_config', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN frontpage_config TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('content_server_api_url', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN content_server_api_url TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('last_request_time', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN last_request_time DATETIME NULL DEFAULT NULL;");
	}
	if(!$o_main->db->field_exists('connection_type', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN connection_type TINYINT(4) NOT NULL DEFAULT '0';");
	}
	if(!$o_main->db->field_exists('app_name', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN app_name TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN app_version TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN app_platform TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN device_name TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN device_year_class TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN sdk_version TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN browser_version TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN screen_resolution TEXT NOT NULL DEFAULT '';");
	}
	if(!$o_main->db->field_exists('getynet_app_id', 'accountinfo'))
	{
		$o_main->db->query("ALTER TABLE accountinfo ADD COLUMN getynet_app_id INT(11) NOT NULL DEFAULT '0';");
	}
	if(!$o_main->db->field_exists('membership_tags', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN membership_tags TEXT NOT NULL DEFAULT '';");
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN active_crm_subscription TINYINT(4) NOT NULL DEFAULT '0';");
	}
	if(!$o_main->db->field_exists('expired', 'session_framework'))
	{
		$o_main->db->query("ALTER TABLE session_framework ADD COLUMN expired TINYINT(4) NOT NULL DEFAULT '0';");
	}

	/*
	** LOAD SESSION
	*/
	require_once(__DIR__.'/index_session.php');
	$v_fw_load_user_session = fw_load_user_session($accountname, $companyID, $_GET['cID'], $v_accountinfo, $v_accountinfo_basisconfig);
	
	if(1 == $v_fw_load_user_session['status'])
	{
		if(isset($_GET['24sevenupd']) && !empty($_SESSION['ASP.NET_SessionId']))
		{
			$o_main->db->query("UPDATE session_framework SET 24sevenintegration_session_id = '".$o_main->db->escape_str($_SESSION['ASP.NET_SessionId'])."' WHERE companyaccessID = '".$o_main->db->escape_str($_GET['cID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'");
			$v_fw_load_user_session['s_url'] = urldecode($_GET['url_param']);
		}
		
		log_action('connect_to_account');
		$s_url = 'pageID=35&accountname='.$accountname.'&companyID='.$companyID.'&'.$v_fw_load_user_session['s_url'];
		header('Location: '.$_SERVER['PHP_SELF'].'?'.$s_url);
	} else {
		echo 'session_is_not_created'.'<br>';
		if(FRAMEWORK_DEBUG) print_r($o_main->db->error());
		log_action('session_is_not_created');
	}
	
	return;
}

$username = $sessionID = '';
if(isset($_COOKIE['username']))
{
	// Membersystem active subscription check
	if(1 == $v_accountinfo['activate_membersystem_subscription_login_check'] && 4 == $variables->fw_session['accesslevel'])
	{
		$b_is_active_membership = FALSE;
		if($o_main->db->table_exists("customer") && $o_main->db->table_exists("contactperson") && $o_main->db->table_exists("subscriptionmulti"))
		{
			$s_sql = "SELECT c.* FROM customer AS c
			JOIN subscriptionmulti AS s ON s.customerId = c.id
			JOIN contactperson AS cp ON cp.customerId = c.id
			WHERE cp.email = '".$o_main->db->escape_str($_COOKIE['username'])."' AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR s.stoppedDate > DATE(NOW())) AND (s.startDate <= DATE(NOW())) GROUP BY c.id";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0)
			{
				$b_is_active_membership = TRUE;
			}
		}
		if(!$b_is_active_membership)
		{
			log_action('no_active_membersystem_subscription');
			echo 'You do not have access to this account';
			exit;
		}
	}
	// Membersystem active subscription check - end
	
	$_GET['caID'] = $_COOKIE[$accountname.'_caID'];
	#Check username before loading page
	$o_fw_user_session = APIconnectorUser('usersessionget', $_COOKIE['username'], $_COOKIE['sessionID']);
	$response = json_decode($o_fw_user_session);

	if(!property_exists($response,'error')) 
	{
		$sessionID = $_SESSION['sessionID'] = $_COOKIE['sessionID'];
		$username = $_SESSION['username'] = $response->data->userName;
		$languageID = $response->data->languageID;
		$userCountry = $response->data->country;
		$userID = $response->data->userID;
		if($response->data->main_company_id > 0)
		{
			$l_main_company_id = $response->data->main_company_id;
		} else {
			$l_main_company_id = $_GET['companyID'];
		}

		include(__DIR__.'/account_fw/menu/refresh_user_session.php'); // This is old session reload!
	}
}
/*
** Reload session if expired
**/
$o_query = $o_main->db->query("SELECT companyaccessID FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' AND expired = 1");
if($o_query && $o_query->num_rows()>0)
{
	if(!function_exists('fw_load_user_session')) include_once(__DIR__.'/index_session.php');
	$v_fw_load_user_session = fw_load_user_session($accountname, $_GET['companyID'], $_GET['caID'], $v_accountinfo, $v_accountinfo_basisconfig);
}

/*
** Change developer mode
**/
if(isset($_POST['fw_setdeveloperaccess']))
{
	$o_main->db->query("UPDATE session_framework SET developeraccess = '".$o_main->db->escape_str($_POST['fw_setdeveloperaccess'])."' WHERE companyaccessID = '".$o_main->db->escape_str(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'");
	log_action('dev_mode_change');
	header('Location: '.$_SERVER['REQUEST_URI']);
	return;
}

/*
** Change language
**/
if(isset($_POST['fw_set_account_language']) && '' != $_POST['fw_set_account_language'])
{
	$v_param = array(
		'COMPANYACCESS_ID'=>(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']),
		'LANGUAGE_ID'=>$_POST['fw_set_account_language'],
	);
	$s_response = APIconnectorUser('companyaccess_set_language', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && 1 == $v_response['status'])
	{
		$o_main->db->query("UPDATE session_framework SET accountlanguageID = '".$o_main->db->escape_str($_POST['fw_set_account_language'])."' WHERE companyaccessID = '".$o_main->db->escape_str(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'");
		$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		log_action('language_change');
	}
	header('Location: '.$_SERVER['REQUEST_URI']);
	return;
}

if(isset($_GET['pageID']) && is_numeric($_GET['pageID']))
{
	$pageID = $_GET['pageID'];
}

$param = array(
	'languageID' => $languageID,
	'userCountry' => $userCountry,
	'pageID' => $pageID,
	'loggID' => $_COOKIE['username'],
	'logget' => 0,
	'sessionID' => $_COOKIE['sessionID'],
	'userID' => $userID
);
$variables = new Variables($param);

$o_main->db->query("UPDATE session_framework SET IP = '".$o_main->db->escape_str($_SERVER['REMOTE_ADDR'])."', last_request_time = NOW() WHERE companyaccessID = '".$o_main->db->escape_str(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'");

$o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' LIMIT 1");
if(!$o_query || $o_query->num_rows()==0 || empty($_GET['caID']))
{
	if(empty($_GET['caID']))
	{
		header('Location: https://www.getynet.com/');
		echo '<center>Access denied. Please <a href="https://www.getynet.com/">login</a>.</center>';
	} else {
		echo 'session_expire'.'<br>';
		log_action('session_expire');
	}
	return;
}
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
$variables->accountinfo_basisconfig = $v_accountinfo_basisconfig;
$variables->fw_url_share = (isset($variables->fw_settings_accountconfig['activate_url_sharing']) && 1 == $variables->fw_settings_accountconfig['activate_url_sharing']);

//account_id stored in accountinfo
if($v_accountinfo['getynet_account_id']){
	$variables->account_id = $v_accountinfo['getynet_account_id'];
} else {
	echo 'getynet_account_error'.'<br>';
	log_action('getynet_account_error');
	return;
}

log_action();
include(__DIR__.'/layout.php');
