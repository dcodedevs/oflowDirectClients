<?php
$b_activate_log = FALSE;
$b_activate_log_privacy = FALSE;
if(isset($o_main->accountinfo['activate_log']) && 1 == $o_main->accountinfo['activate_log'])
{
	if(!$o_main->db->table_exists('sys_log'))
	{
		$b_activate_log = $o_main->db->simple_query("CREATE TABLE sys_log (
			".($o_main->multi_acc?"account_id INT NOT NULL DEFAULT '0',":"")."
			created TIMESTAMP NOT NULL,
			username CHAR(100) NOT NULL,
			session_id CHAR(100) NOT NULL,
			ip CHAR(100) NOT NULL,
			url TEXT NOT NULL,
			post TEXT NOT NULL,
			referer TEXT NOT NULL,
			task CHAR(100) NOT NULL,
			module CHAR(50) NOT NULL,
			INDEX Idx (created, username, ip)".($o_main->multi_acc?",INDEX account_Idx (account_id)":"")."
		) ENGINE=MyISAM;");
	} else {
		if(!$o_main->db->field_exists("module", "sys_log"))
		{
			$o_main->db->simple_query("ALTER TABLE sys_log ADD COLUMN module CHAR(50) NOT NULL AFTER task");
		}
		if($o_main->multi_acc && !$o_main->db->field_exists("account_id", "sys_log"))
		{
			$o_main->db->simple_query("ALTER TABLE sys_log ADD COLUMN account_id INT NOT NULL DEFAULT 0 FIRST");
			$o_main->db->simple_query("ALTER TABLE sys_log ADD INDEX account_Idx (account_id);");
			$o_main->db->simple_query("ALTER TABLE sys_log ENGINE=MyISAM;");
		}
		$b_activate_log = TRUE;
	}
	if($b_activate_log) $b_activate_log_privacy = (isset($o_main->accountinfo['activate_log_privacy']) && 1 == $o_main->accountinfo['activate_log_privacy']);
}
defined('LOG_ACTION') or define('LOG_ACTION', $b_activate_log);
defined('LOG_ACTION_PRIVATE') or define('LOG_ACTION_PRIVATE', $b_activate_log_privacy);

if(!function_exists('log_action')){
function log_action($s_task = "page_request", $s_username = NULL, $s_session_id = NULL, $s_url = NULL, $v_post = NULL)
{
	$o_main = get_instance();
	if(LOG_ACTION)
	{
		if($s_username == NULL) $s_username = (isset($_COOKIE['username']) ? $_COOKIE['username'] : '');
		if($s_session_id == NULL) $s_session_id = (isset($_COOKIE['sessionID']) ? $_COOKIE['sessionID'] : '');
		if($s_url == NULL) $s_url = (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '');
		if($v_post == NULL) $v_post = $_POST;
		$s_ip_address = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
		$s_referer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
		$s_module = (isset($_GET['module']) ? $_GET['module'] : '');
		
		if(LOG_ACTION_PRIVATE)
		{
			$s_username = $s_ip_address = '';
		}
		
		$s_sql = "INSERT INTO sys_log SET
		created = NOW(),
		username = '".$o_main->db->escape_str($s_username)."',
		session_id = '".$o_main->db->escape_str($s_session_id)."',
		ip = '".$o_main->db->escape_str($s_ip_address)."',
		url = '".$o_main->db->escape_str($s_url)."',
		post = '".$o_main->db->escape_str(json_encode($v_post))."',
		referer = '".$o_main->db->escape_str($s_referer)."',
		task = '".$o_main->db->escape_str($s_task)."',
		module = '".$o_main->db->escape_str($s_module)."'".($o_main->multi_acc?", account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
		$o_main->db->query($s_sql);
	}
}
}