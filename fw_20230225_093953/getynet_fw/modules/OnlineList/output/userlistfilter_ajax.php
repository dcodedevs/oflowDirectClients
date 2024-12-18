<?php
if(isset($_COOKIE['username'],$_COOKIE['sessionID']))
{
	if(!function_exists("APIconnectorUser")) if(is_file(__DIR__."/../../../includes/APIconnector.php")) include_once(__DIR__."/../../../includes/APIconnector.php");

	$s_file = __DIR__."/../../../outputLanguages/".$_GET['dlang'].".php";
	if(is_file($s_file)) include($s_file);
	$s_file = __DIR__."/../../../outputLanguages/".$_GET['lang'].".php";
	if(is_file($s_file)) include($s_file);

	$UserContactSet = APIconnectorUser("contactsetget", $_COOKIE['username'], $_COOKIE['sessionID'], array('SHOW_SET'=>$_GET['set'],'SHOW_COMPANY'=>$_GET['company']));
	
	define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	
	$s_sql = 'UPDATE session_framework SET cache_contactset = ? WHERE companyaccessID = ? AND session = ? AND username = ?';
	$o_query = $o_main->db->query($s_sql, array($UserContactSet, (isset($_GET['caID'])?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']), $_COOKIE['sessionID'], $_COOKIE['username']));

	$UserContactSet = json_decode($UserContactSet,true);

	$UserContactSet['current']="";
	if($UserContactSet['showAll']==1)
	{
		$UserContactSet['current'] = $formText_All_RightSide."</td><td>";
	} else {
		foreach($UserContactSet['sets'] as $item)
		{
			if($item['active'] == 1)
			{
				if($UserContactSet['current']!="") $UserContactSet['current'] .="<br />";
				$UserContactSet['current'] .= '<li>'.$item['name']. "</li>";
			}
		}
	}
}
?>