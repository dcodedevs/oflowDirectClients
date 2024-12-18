<?php
//Not allowed functionality
print "ERROR";
exit;
include(__DIR__."/../../../../../dbConnect.php");
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../includes/APIconnect.php");
$accountinfo = mysql_fetch_assoc(mysql_query("select * from accountinfo"));
$settings = mysql_fetch_assoc(mysql_query("select * from settings"));

//print_r($_GET);
$response = json_decode(APIconnectAccount("companysearchlist", $accountinfo['accountname'], $accountinfo['password'], array("PARTNER_ID"=>$settings['partnerID'], "PARTNER_PWD"=>$settings['partnerPassword'], "SEARCH"=>$_GET['searchtext'])), true);

if(array_key_exists('data',$response))
{
	if(sizeof($response['data'])==0)
	{
		print "NO_RESULT";
	} else {
		foreach($response['data'] as $item)
		{
			?><div><a class="script" href="javascript:<?=$_GET['fnctn'];?>('<?=$item['id'];?>');"><?=$item['name'].' ('.$item['paCity'].', '.$item['paCountry'].')';?></a></div><?php
		}
	}
} else {
	print "ERROR";
}
?>