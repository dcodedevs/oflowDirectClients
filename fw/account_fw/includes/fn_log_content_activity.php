<?php
if(!$o_main->db->table_exists('content_activity_log'))
{
	$b_activate_log = $o_main->db->simple_query("CREATE TABLE content_activity_log (
		created TIMESTAMP NOT NULL,
		username CHAR(100) NOT NULL,
		message TEXT NOT NULL,
		module_name CHAR(100) NOT NULL DEFAULT '',
		content_table CHAR(50) NOT NULL DEFAULT '',
		content_id INT(11) NOT NULL DEFAULT 0,
		action TINYINT(4) NOT NULL DEFAULT 0 COMMENT '1 - created; 2 - edited; 3 - deleted; 4 - view; 5 - other + message',
		connection_type TINYINT(4) NOT NULL DEFAULT 0 COMMENT '1 - desktop; 2 - mobile',
		INDEX relation_Idx (username, created),
		INDEX module_Idx (module_name)
	)");
}

function log_content_activity($action, $module_name, $content_table = '', $content_id = 0, $message = '', $username = NULL, $connection_type = 1, $log_once = TRUE, $update_last = FALSE)
{
	if(NULL === $username) $username = (isset($_COOKIE['username']) ? $_COOKIE['username'] : '');
	$o_main = get_instance();
	if($log_once)
	$o_query = $o_main->db->query("SELECT created FROM content_activity_log WHERE username = '".$o_main->db->escape_str($username)."' AND
		module_name = '".$o_main->db->escape_str($module_name)."' AND
		content_table = '".$o_main->db->escape_str($content_table)."' AND
		content_id = '".$o_main->db->escape_str($content_id)."' AND
		action = '".$o_main->db->escape_str($action)."' AND
		connection_type = '".$o_main->db->escape_str($connection_type)."'");
	if($log_once && $o_query && $o_query->num_rows()>0)
	{
		if($update_last)
		$o_main->db->query("UPDATE content_activity_log SET created = NOW() WHERE username = '".$o_main->db->escape_str($username)."' AND
		module_name = '".$o_main->db->escape_str($module_name)."' AND
		content_table = '".$o_main->db->escape_str($content_table)."' AND
		content_id = '".$o_main->db->escape_str($content_id)."' AND
		action = '".$o_main->db->escape_str($action)."' AND
		connection_type = '".$o_main->db->escape_str($connection_type)."'");
	} else {
		$o_main->db->query("INSERT INTO content_activity_log SET created = NOW(),
		username = '".$o_main->db->escape_str($username)."',
		message = '".$o_main->db->escape_str($message)."',
		module_name = '".$o_main->db->escape_str($module_name)."',
		content_table = '".$o_main->db->escape_str($content_table)."',
		content_id = '".$o_main->db->escape_str($content_id)."',
		action = '".$o_main->db->escape_str($action)."',
		connection_type = '".$o_main->db->escape_str($connection_type)."'");
	}
}