<?php
/**
 * Version 8.0
**/
function APIconnectorUser($command, $username, $sessionID, $parameters = array())
{
	$jsonData['USERNAME'] = $username;
	$jsonData['SESSION_ID'] = $sessionID;
	$jsonData['IP'] = $_SERVER['REMOTE_ADDR'];
	$jsonData['PARM'] = $parameters;
	$jsonData['COMMAND'] = strtolower($command);
	$data = array('data'=>json_encode($jsonData));
	
	$url = 'https://api.getynet.com/user/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}


function APIconnectorAccount($command, $accountName, $accountPassword, $parameters = array())
{
	$jsonData = array();
	$jsonData['ACC_NAME'] = $accountName;
	$jsonData['ACC_PASSWORD'] = $accountPassword;
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	
	$jsonData['COMMAND'] = strtolower($command);
	$jsonData['PARM'] = $parameters;
	$data = array('data'=>json_encode($jsonData));
	
	$url = 'https://api.getynet.com/account/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}


function APIconnectorOpen($command, $parameters = array())
{
	$jsonData = array();
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	
	$jsonData['COMMAND'] = strtolower($command);
	$jsonData['PARM'] = $parameters;
	$data = array('data'=>json_encode($jsonData));
	
	$url = 'https://api.getynet.com/open/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}


function APIconnectorServer($command, $server, $serverPassword, $parameters = array())
{
	$jsonData = array();
	$jsonData['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	$jsonData['SERVER_PWD'] = $serverPassword;
	
	$jsonData['COMMAND'] = strtolower($command);
	$jsonData['PARM'] = $parameters;
	$data = array('data'=>json_encode($jsonData));
	
	$url = 'https://'.$server.'/serverapi/index.php';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	return $response;
}
?>