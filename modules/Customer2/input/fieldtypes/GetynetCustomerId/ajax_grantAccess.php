<?php
//Not allowed functionality
print "ERROR";
exit;
include(__DIR__."/../../../../../dbConnect.php");
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../includes/APIconnect.php");
$accountinfo = mysql_fetch_assoc(mysql_query("select * from accountinfo"));
$settings = mysql_fetch_assoc(mysql_query("select * from settings"));

//print_r($_POST);
$response = json_decode(APIconnectUser("accountaccessaddtomyself", $_COOKIE['username'], $_COOKIE['sessionID'], array("PARTNER_ID"=>$settings['partnerID'], "PARTNER_PWD"=>$settings['partnerPassword'], "COMPANY_ID"=>$_POST['companyID'], 'DEVELOPER_ACCESS'=>(isset($_POST['developer'])?1:0) )), true);

if(array_key_exists('data',$response))
{
	print $response['data'];
} else {
	print $response['error'];
}
?>