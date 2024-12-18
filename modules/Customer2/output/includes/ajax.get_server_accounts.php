<?php
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$v_accountinfo = $o_query->row_array();
}
$s_sql = "select * from customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$v_customer_accountconfig = $o_query->row_array();
}
$v_param = array
(
	"PARTNER_ID"=>$v_customer_accountconfig['getynet_partner_id'],
	"PARTNER_PWD"=>$v_customer_accountconfig['getynet_partner_pw'],
	"SERVER_ID"=>$_GET['server']
);

$s_response = APIconnectAccount("serverlibraryaccountgetlist", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
$v_response = json_decode($s_response, true);
if(array_key_exists('data', $v_response))
{
	$v_accounts = $v_response['data'];
	natcasesort($v_accounts);
	$fw_return_data = "<option value=\"\">".$formText_Choose_Output."</option>";
	foreach($v_accounts as $s_account)
	{
		$fw_return_data .= "<option value=\"".$s_account."\">".$s_account."</option>";
	}
} else {
	echo $formText_ErrorOccuredProcessingRequest_Output;
}
?>