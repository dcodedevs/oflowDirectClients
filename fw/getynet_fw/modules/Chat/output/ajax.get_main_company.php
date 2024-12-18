<?php
$s_api_file = __DIR__ .'/../../../includes/APIconnector.php';
if(!function_exists("APIconnectorUser") && file_exists($s_api_file)) require_once($s_api_file);

$l_main_company_id = $_GET['main_company_id'];

$s_response = APIconnectorUser("company_get_list", $_COOKIE['username'], $_COOKIE['sessionID']);
$v_companies = json_decode($s_response, true);
if(isset($v_companies['data']) && count($v_companies['data']) > 0)
{
	$v_search = array();
	?><select id="fw_chat_company_id"><?php
	foreach($v_companies['data'] as $v_item)
	{
		$v_search[] = $v_item['companyID'];
		?><option value="<?php echo $v_item['companyID'];?>"<?php echo ($l_main_company_id == $v_item['companyID'] ? ' selected':'');?>><?php echo $v_item['companyname'];?></option><?php
	}
	?></select><?php
	if(!in_array($l_main_company_id, $v_search))
	{
		$l_main_company_id = $v_search[0];
		APIconnectorUser("user_main_company_set", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=> $l_main_company_id));
	}
	unset($v_search);
} else {
	APIconnectorUser("user_main_company_set", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=> $l_main_company_id));
}