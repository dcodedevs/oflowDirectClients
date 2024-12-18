<?php
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
require_once ACCOUNT_PATH . '/elementsGlobal/cMain.php';

$curl = curl_init();
$headers = array(
	'Content-Type: application/json',
);
$params = array("response"=>array("uid"=>3,"message"=>"ok"));
curl_setopt($curl, CURLOPT_URL, "https://s27.getynet.com/accounts/oflowDirectClients/modules/IntegrationJCloud/output/handler.php");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($curl);
$response_decoded = trim($response);
$responseInfo = curl_getinfo($curl);
var_dump($response_decoded);
?>
