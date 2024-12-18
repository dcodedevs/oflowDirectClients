<?php
/*
**
** This script is for internal input usage
**
**
**
*/
// v - 7.1.0
//
function APIconnectUser($command, $username, $sessionID, $parameters = array())
{
	$jsonData['USERNAME'] = $username;
	$jsonData['SESSION_ID'] = $sessionID;
	$jsonData['IP'] = $_SERVER['REMOTE_ADDR'];
	$jsonData['PARM'] = $parameters;
	$jsonData['COMMAND'] = strtolower($command);
	$data = array('data'=>json_encode($jsonData));
//	print_r($data);
	//call api 
	$url = 'https://api.getynet.com/user/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	$tmp = json_decode($response,true);
	if(isset($tmp['main_error']))
	{
		print '<script type="text/javascript">alert("API error: '.str_replace('"','\"',$tmp['main_error']).'");';
		if(isset($tmp['session_expire'])) print 'window.location.replace("http://www.getynet.com");';
		print '</script>';
	}
	
	return $response;
}


function APIconnectAccount($command, $accountName, $accountPassword, $parameters = array())
{
	$jsonData = array();
	$jsonData['ACC_NAME'] = $accountName;
	$jsonData['ACC_PASSWORD'] = $accountPassword;
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	
	$jsonData['COMMAND'] = strtolower($command);
	$jsonData['PARM'] = $parameters;
	$data = array('data'=>json_encode($jsonData));//print_r($data);
	
	//call api
	$url = 'https://api.getynet.com/account/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);

	curl_close($ch);//echo "response = $response<br />";
	
	return $response;
}


function APIconnectOpen($command, $parameters = array())
{
	$jsonData = array();
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	
	$jsonData['COMMAND'] = strtolower($command);
	$jsonData['PARM'] = $parameters;
	$data = array('data'=>json_encode($jsonData));
	
	//call api
	$url = 'https://api.getynet.com/open/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}


function APIconnectServer($command, $server, $serverPassword, $parameters = array())
{
	$jsonData = array();
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	$jsonData['SERVER_PWD'] = $serverPassword;
	
	$jsonData['COMMAND'] = strtolower($command);
	$jsonData['PARM'] = $parameters;
	$data = array('data'=>json_encode($jsonData));
	
	//call api
	$url = "http://{$server}/serverapi/index.php";
	$ch = curl_init($url);
	//echo "url = $url";
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	//echo "response = $response";
	curl_close($ch);
	
	return $response;
}
?>