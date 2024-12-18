<?php
/*
 *
 * Version 8.2
 *
 * Parameters
 * $_GET['module']
 * $_GET['unsubscribe']
 * $_GET['l']
 * $_GET['track']
 * $_GET['link']
 * $_GET['p']
 * $_GET['f']
 */

session_start();
define('BASEPATH', __DIR__.DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(!$o_main->db->table_exists('sys_emailsendtrack'))
{
	$o_main->db->simple_query("CREATE TABLE sys_emailsendtrack (
		track_id CHAR(50) NOT NULL,
		track_action TINYINT(4) NOT NULL DEFAULT 0,
		session_id VARCHAR(100) NOT NULL DEFAULT '',
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		ip_address VARCHAR(50) NOT NULL DEFAULT '',
		link VARCHAR(1000) NOT NULL DEFAULT '',
		browser CHAR(255) NULL DEFAULT '',
		INDEX Idx (track_id, track_action)
	);");
}

if(isset($_GET['track']))
{
	$v_param_check = array();
	$s_track_id = urldecode($_GET['track']);
	$s_sql_check = "SELECT id FROM sys_emailsendtrack WHERE track_id = ? AND session_id = ? AND ip_address = ? AND track_action = ? AND created > DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
	$s_sql = "INSERT INTO sys_emailsendtrack(track_id, track_action, session_id, created, ip_address, link, browser) VALUES(?, ?, ?, NOW(), ?, ?, ?)";
	$v_param = array($s_track_id, 1, session_id(), $_SERVER['REMOTE_ADDR'], urldecode($_GET['link']), $_SERVER['HTTP_USER_AGENT']);
	if(isset($_GET['link']))
	{
		$v_param[1] = 4;
	} else if(isset($_GET['f'])) {
		$v_param[1] = 3;
		$v_param_check = array($s_track_id, session_id(), $_SERVER['REMOTE_ADDR'], 3);
	} else if(isset($_GET['p'])) {
		$v_param[1] = 2;
		$v_param_check = array($s_track_id, session_id(), $_SERVER['REMOTE_ADDR'], 2);
	} else {
		$v_param_check = array($s_track_id, session_id(), $_SERVER['REMOTE_ADDR'], 1);
		
	}
	$b_check = true;
	if($s_track_id == 'EMAIL_TRACKING_CODE_KEY') $b_check = false;
	if(count($v_param_check)>0)
	{
		$o_query = $o_main->db->query($s_sql_check, $v_param_check);
		if($o_query && $o_query->num_rows()>0) $b_check = false;
	}
	
	if($b_check) $o_main->db->query($s_sql, $v_param);
	
	if(isset($_GET['link']))
	{
		header('Location: '.urldecode($_GET['link']));
	} else {
		//send transparent image
		$img = imagecreatetruecolor(1,1);
		imagesavealpha($img, true);
		imagefill($img, 0, 0, imagecolorallocatealpha($img,0x00,0x00,0x00,127));
		header('Content-Type: image/png');
		imagepng($img);
		imagedestroy($img); 
	}
} else {
	if(isset($_GET['open']))
	{
		$s_track_id = urldecode($_GET['open']);
		if($s_track_id != 'EMAIL_TRACKING_CODE_KEY')
		{
			$s_sql = "INSERT INTO sys_emailsendtrack(track_id, track_action, session_id, created, ip_address, link, browser) VALUES(?, ?, ?, NOW(), ?, ?, ?)";
			$v_param = array($s_track_id, 5, session_id(), $_SERVER['REMOTE_ADDR'], urldecode($_GET['link']), $_SERVER['HTTP_USER_AGENT']);
			$o_main->db->query($s_sql, $v_param);
		}
	}
	$s_file = BASEPATH.'modules/'.(isset($_GET['module']) ? str_replace(array('.','/'),'',$_GET['module']) : 'EmailMarketingApp').'/output_browser/output.php';
	if(is_file($s_file))
	{
		include($s_file);
	} else {
		echo 'Newsletter is not configured!';
	}
}
$o_main->db->close();
?>