<?php
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

if(isset($_GET['token']) && isset($_GET['clientId'])){
	/*$o_query = $o_main->db->query("SELECT * FROM accountinfo".($o_main->multi_acc?" WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""));
	$v_accountinfo = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
	if('oflowDirectClients' == $v_accountinfo['accountname'] && ($_SERVER['REMOTE_ADDR']=='87.110.235.137')
	)
	{
		$s_sql = "SELECT * FROM creditor WHERE 24sevenoffice_client_id = '".$o_main->db->escape_str($_GET['clientId'])."'";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0)
		{
			$v_row = $o_query->row_array();
			if(1 == (int)$v_row['environment']) // Beta
			{
			}
			if(2 == (int)$v_row['environment']) // Development
			{
				$page_url   = 'http';
				if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
					$page_url .= 's';
				}
				header('Location: '.$page_url.'://'.str_replace('s30.', 's32.', $_SERVER['SERVER_NAME']).str_replace('oflowDirectClients', 'oflowDirectClientsDev', $_SERVER['REQUEST_URI']));
				exit;
			}
		}
	}*/
	require_once __DIR__ . '/../internal_api/load.php';
	$api = new Integration24SevenOffice(array(
		//'ownercompany_id' => 1,
		'token' => $_GET['token'],
		'clientId' => $_GET['clientId'],
		'supplierId'=> $_GET['supplierId'],
		'o_main' => $o_main,
		'previous' => isset($_GET['previous']) ? $_GET['previous'] : ''
	));
	if(isset($_SESSION['redirect_url']) && '' != $_SESSION['redirect_url'])
	{
		header('Location: '.$_SESSION['redirect_url']);
	} else {
		echo 'No access';
	}
} else if(isset($_GET['t']))
{
	require_once __DIR__ . '/../internal_api/load.php';
	$api = new Integration24SevenOffice(array(
		//'ownercompany_id' => 1,
		'token' => $_GET['t'],
		'o_main' => $o_main,
		'previous' => isset($_GET['previous']) ? $_GET['previous'] : ''
	));
	if(isset($_SESSION['redirect_url']) && '' != $_SESSION['redirect_url'])
	{
		header('Location: '.$_SESSION['redirect_url']);
	} else {
		echo 'No access';
	}
} else {
	echo 'No access';
}
