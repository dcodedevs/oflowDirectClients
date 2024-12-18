<?php
if(isset($_COOKIE['username'], $_COOKIE['sessionID']))
{
	if(!function_exists("APIconnectorUser")) if(is_file(__DIR__."/../../../includes/APIconnector.php")) include_once(__DIR__."/../../../includes/APIconnector.php");
	
	$data = json_decode(APIconnectorUser("userstatusset", $_COOKIE['username'], $_COOKIE['sessionID'], array('NEW_STATUS'=>$_GET['userstatus'])),true);
	
	$data = json_decode(APIconnectorUser("userstatusmessageset", $_COOKIE['username'], $_COOKIE['sessionID'], array('MESSAGE'=>trim($_GET['userstatusmessage']))),true);
	
	$d = explode(".",$_GET['userstatusmsgdate']);
	$data = json_decode(APIconnectorUser("userstatusmessageshowtimeset", $_COOKIE['username'], $_COOKIE['sessionID'], array('NEW_DATETIME'=>"{$d[2]}-{$d[1]}-{$d[0]} {$_GET['userstatusmsgtime']}:00")),true);
	
	foreach($_GET['userstatussmgshow'] as $item)
	{
		list($type,$id) = explode("_",$item);
		$UserMessageShowto = json_decode(APIconnectorUser("messageshowtogetset", $_COOKIE['username'], $_COOKIE['sessionID'], array('ID'=>$id,'TYPE'=>$type)),true);
		if($item=="set_0") break;
	}
	
	
	$dataUserstatus = APIconnectorUser("usersessionget", $_COOKIE['username'], $_COOKIE['sessionID']);
	$dataMessageShowto = APIconnectorUser("messageshowtogetset", $_COOKIE['username'], $_COOKIE['sessionID'], array('ID'=>-1, 'TYPE'=>-1));
	
	define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	
	$s_sql = 'UPDATE session_framework SET cache_userstatus = ? WHERE companyaccessID = ? AND session = ? AND username = ?';
	$o_query = $o_main->db->query($s_sql, array($dataUserstatus, (isset($_GET['caID'])?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']), $_COOKIE['sessionID'], $_COOKIE['username']));

	$data = json_decode($dataUserstatus,true);
	$userstatus = $data['data'];
	
	$ret = array('status'=>$data['data']['status']);
	if($data['data']['statusMessage']!="") $ret['statusmessage'] = $data['data']['statusMessage'];
	print json_encode($ret);
}
?>