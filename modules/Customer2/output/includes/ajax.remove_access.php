<?php
if($moduleAccesslevel > 10)
{
	if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

	$s_sql = "select * from accountinfo";
	$o_query = $o_main->db->query($s_sql);
	$v_accountinfo = $o_query ? $o_query->row_array() : array();

	$s_sql = "select * from contactperson where id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	$v_row = $o_query ? $o_query->row_array() : array();
	
	$s_sql = "select * from customer where id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
	$v_customer = $o_query ? $o_query->row_array() : array();
	
	$o_query = $o_main->db->query("SELECT * FROM customer_basisconfig");
	$v_customer_config = $o_query ? $o_query->row_array() : array();
	$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_customer_config = $o_query->row_array();
	}
	
	if(isset($v_customer['selfdefined_company_id']))
	{
		$s_response = APIconnectAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			foreach($v_response['items'] as $v_item)
			{
				if($v_customer['selfdefined_company_id'] == $v_item['id'] && $v_item['invitation_config'] != '')
				{
					$variables->invitation_config = $v_item['invitation_config'];
					$variables->invitation_config_accesslevel = $v_item['invitation_config_accesslevel'];
					$variables->invitation_config_groupID = $v_item['invitation_config_groupID'];
					$variables->invitation_config_contentIdField = $v_item['invitation_config_contentIdField'];
					$variables->invitation_config_moduleName = $v_item['invitation_config_moduleName'];
				}
			}
		}
	}
	$accesslevel = "";
	if($v_customer_config['invitation_accesslevel'] != "") {
		$accesslevel = $v_customer_config['invitation_accesslevel'];
	}
	if($variables->invitation_config_accesslevel > 0) {
		$accesslevel = $variables->invitation_config_accesslevel;
	}
	$contentIdField = $v_customer_config['invitation_contentIdField'];
	if($variables->invitation_config_contentIdField != "") {
		$contentIdField = $variables->invitation_config_contentIdField;
	}


	$l_membersystem_id = $v_row[$contentIdField];
	$s_receiver_name = $v_row['name'];
	$s_receiver_email = $v_row['email'];

	$o_response = json_decode(APIconnectAccount("membersystemcompanyaccessdelete", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$s_receiver_email, "MEMBERSYSTEMID"=>$l_membersystem_id, "ACCESSLEVEL"=>$accesslevel)));

	if($_POST['return_data'] == 1)
	{
		$fw_return_data['result'] = ($o_response->data == "OK" ? 1 : 0);
		return;
	}
}
?>
<div class="popupform">
	<div class="popupfromTitle"><?php echo $formText_RemoveAccess_Output;?></div>
	<div><?php
	if($o_response->data == "OK")
	{
		echo $formText_AccessRemoved_Output;
	} else {
		echo $formText_ErrorOccured_Output.": ".$o_response->error;
	}
	?></div>
</div>
<div class="popupformbtn">
	<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
</div>
<style>
#popupeditbox {
	background-color:#FFFFFF;
	border-radius:4px;
	color:#111111;
	display:none;
	padding:25px;
	width:600px;
}
#popupeditbox .button {
	background-color:#0393ff;
	color:#fff;
	cursor:pointer;
	display:inline-block;
	padding:10px 20px;
	text-align:center;
	text-decoration:none;
}
#popupeditbox .button:hover {
	background-color:#1e1e1e;
}
#popupeditbox .button.b-close,
#popupeditbox .button.bClose {
	position:absolute;
	border: 3px solid #fff;
	-webkit-border-radius: 100px;
	-moz-border-radius: 100px;
	border-radius: 100px;
	padding: 0px 9px;
	font: bold 100% sans-serif;
	line-height: 25px;
	right:-10px;
	top:-10px
}
#popupeditbox .button > span {
	font: bold 100% sans-serif;
	font-size: 12px;
	line-height: 12px;
}
.popupform {
	border: 0;
}
.popupfromTitle {
	font-size: 24px;
	padding: 0px 0px 10px;
	border-bottom: 1px solid #ededed;
	color: #5d5d5d;
	margin-bottom: 15px;
}
.popupformbtn {
	text-align:right;
	margin-top:20px;
	position:relative;
}
.popupformbtn .submitbtn {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
</style>
