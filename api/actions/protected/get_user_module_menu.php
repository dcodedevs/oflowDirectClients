<?php
$o_query = $o_main->db->get('accountinfo');
$variables->accountinfo = $o_query ? $o_query->row_array() : array();

$variables->fw_session = $v_fw_session;
$variables->menu_access = json_decode($v_fw_session['cache_menu'], TRUE);
$variables->languageID = $v_fw_session['accountlanguageID'];
$variables->loggID = $v_data['username'];
$variables->developeraccess = 0;
$variables->useradmin = $v_fw_session['useradmin'];
$variables->system_admin = $v_fw_session['system_admin'];
$variables->fw_url_share = TRUE;
$_GET['pageID'] = 35;
$_GET['accountname'] = $variables->accountinfo['accountname'];
$_GET['companyID'] = $v_data['companyID'];
$_GET['caID'] = $v_data['caID'];
$username = $v_data['username'];
$sessionID = $v_data['sessionID'];

// Get framework settings
$o_query = $o_main->db->get('frameworksettings_basisconfig');
$variables->fw_settings_basisconfig = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT *, IF(id = '".$o_main->db->escape_str($v_fw_session['style_set'])."', 0, 1) AS priority FROM frameworksettings WHERE id = '".$o_main->db->escape_str($v_fw_session['style_set'])."' OR id > 0 ORDER BY priority, id LIMIT 1");
$variables->fw_settings_accountconfig = $o_query ? $o_query->row_array() : array();

if(isset($variables->fw_settings_accountconfig['activate_module_hiding_script']) && is_file(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']))
{
	$_COOKIE['username'] = $v_data['username'];
	include(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']);
}

$page_url   = 'http';
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
	$page_url .= 's';
}
$_SERVER['PHP_SELF'] = $page_url.'://'.$_SERVER['SERVER_NAME'].'/accounts/'.$variables->accountinfo['accountname'].'/fw/index.php';

$v_menu_list = array();
include(BASEPATH.'fw/account_fw/menu/output_menu_json.php');
ob_clean();
$v_return['menu'] = $v_menu_list;
