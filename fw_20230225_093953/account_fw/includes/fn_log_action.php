<?php
$b_activate_log = FALSE;
$b_activate_log_privacy = FALSE;
if($o_main->db->field_exists('activate_log', 'accountinfo'))
{
	$o_query = $o_main->db->query('SELECT * FROM accountinfo WHERE activate_log = 1');
	if($o_query && $o_query->num_rows()>0)
	{
		$v_config = $o_query->row_array();
		if(!$o_main->db->table_exists('sys_log'))
		{
			$b_activate_log = $o_main->db->simple_query('CREATE TABLE sys_log (
				created TIMESTAMP NOT NULL,
				username CHAR(100) NOT NULL,
				session_id CHAR(100) NOT NULL,
				ip CHAR(100) NOT NULL,
				url TEXT NOT NULL,
				post TEXT NOT NULL,
				referer TEXT NOT NULL,
				task CHAR(100) NOT NULL,
				module CHAR(50) NOT NULL,
				INDEX Idx (created, username, ip)
			)');
		} else {
			if(!$o_main->db->field_exists("module", "sys_log"))
			{
				$o_main->db->simple_query('ALTER TABLE sys_log ADD COLUMN module CHAR(50) NOT NULL AFTER task');
			}
			$b_activate_log = TRUE;
		}
		if($b_activate_log) $b_activate_log_privacy = (isset($v_config['activate_log_privacy']) && 1 == $v_config['activate_log_privacy']);
	}
}
defined('LOG_ACTION') or define('LOG_ACTION', $b_activate_log);
defined('LOG_ACTION_PRIVATE') or define('LOG_ACTION_PRIVATE', $b_activate_log_privacy);

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
		
		$v_param = array($s_username, $s_session_id, $s_ip_address, $s_url, json_encode($v_post), $s_referer, $s_task, $s_module);
		$o_main->db->query('INSERT INTO sys_log (created, username, session_id, ip, url, post, referer, task, module) VALUES(NOW(), ?, ?, ?, ?, ?, ?, ?, ?)', $v_param);
	}
}