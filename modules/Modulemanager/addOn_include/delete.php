<?php
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../input/includes/ftp_commands.php");
if(!function_exists("get_developer_access")) include(__DIR__."/../input/includes/fn_get_developer_access.php");

if(get_developer_access() >= 20)
{
	$deletedir =  "/modules/".$_GET['deletemodule'];
	ftp_delete_directory($deletedir,1);
	
	$o_main->db->query("delete from moduledata where name = ?", array($_GET['deletemodule']));
	
	# Update Cache
	$fw_session = array();
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$menuaccess = json_decode($fw_session['cache_menu'], true);
	unset($menuaccess[$_GET['deletemodule']]);
	$o_main->db->update('session_framework', array('cache_menu' => json_encode($menuaccess)), $v_param);
} else {
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_main->db->update('session_framework', array('error_msg' => json_encode(array('Access denied!'))), $v_param);
}

header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']);
exit;
?>