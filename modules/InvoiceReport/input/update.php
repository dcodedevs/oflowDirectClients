<?php
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
// function for geting current script GET params as string
include_once (__DIR__."/includes/fnctn_get_curent_GET_params.php");
if(!function_exists("log_action")) include(__DIR__."/includes/fn_log_action.php");

if($o_main->db->table_exists($_GET['updatemodule']) && $o_main->db->field_exists($_GET['updatefield'], $_GET['updatemodule']))
{
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$menuaccess = json_decode($fw_session['cache_menu'],true);
	$access = $menuaccess[$_GET['module']][2];
	$b_owner_access = ($menuaccess[$_GET['module']][6] == 1 ? true : false);
	if($fw_session['developeraccess'] >= 20)
	{
		$access = 111;
		$b_owner_access = false;
	}
	
	$ID = intval($_GET['updateID']);
	
	// check owner access
	if($b_owner_access)
	{
		$o_query = $o_main->db->query('SELECT createdBy FROM '.$_GET['updatemodule'].' WHERE id = ?', array($ID));
		if($o_query && $o_row = $o_query->row())
		{
			if($o_row->createdBy != $username) $access = $access % 10;
		}
	}
	
	if($access >= 10)
	{
		if($ID > 0)
		{
			$o_main->db->update($_GET['updatemodule'], array($_GET['updatefield'] => $_GET['updatevalue']), array('id' => $ID));
		}
		
		$s_return_url = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module={$_GET['module']}";
		
		if(isset($_GET['submodule'])){
			$s_return_url .= "&submodule={$_GET['submodule']}";
		}
		if(isset($_GET['relationID']))
		{
			$s_return_url .="&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield'];
		}
		if(isset($_GET["content_status"]))
		{
			$s_return_url .= "&content_status=".$_GET['content_status'];
		}
		
		log_action("content_update");
		header('Location: '.$s_return_url);
		exit;
	} else {
		log_action("content_update_fail");
		?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField" >You have no access to this module</td></tr></table></div><?php
	}
} else {
	log_action("table_does_not_exist");
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField" >Wrong parameters</td></tr></table></div><?php
}
?>