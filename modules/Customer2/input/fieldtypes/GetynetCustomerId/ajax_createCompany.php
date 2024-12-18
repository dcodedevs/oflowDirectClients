<?php
//Not allowed functionality
print "ERROR";
exit;
include(__DIR__."/../../../../../dbConnect.php");
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../includes/APIconnect.php");
$accountinfo = mysql_fetch_assoc(mysql_query("select * from accountinfo"));
$settings = mysql_fetch_assoc(mysql_query("select * from settings"));

$response = json_decode(APIconnectAccount("companycreatenew", $accountinfo['accountname'], $accountinfo['password'],
	array("PARTNER_ID"=>$settings['partnerID'],
		"PARTNER_PWD"=>$settings['partnerPassword'],
		"COMPANYNR"=>$_GET['COMPANYNR'],
		"COMPANYNAME"=>$_GET['COMPANYNAME'],
		"LANGUAGEID"=>"no",/*$_GET['LANGUAGEID'],*/
		"ADRESSLINE1"=>$_GET['ADRESSLINE1'],
		"ADRESSLINE2"=>$_GET['ADRESSLINE2'],
		"POSTALCODE"=>$_GET['POSTALCODE'],
		"CITY"=>$_GET['CITY'],
		"COUNTRY"=>$_GET['COUNTRY'],
		"PHONE"=>$_GET['PHONE'],
		"FAX"=>$_GET['FAX'],
		"EMAIL"=>$_GET['EMAIL'],
		"FORCE"=>$_GET['FORCE'])),true);

if(array_key_exists('data',$response))
{
	print $response['data']['companyID'];
} else if($_GET['FORCE'] <> 1) {
	print "CUSTOMER_EXISTS";
} else {
	print "ERROR";
}
?>