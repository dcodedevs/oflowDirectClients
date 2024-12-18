<?php
$s_error_msg = "";
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();

$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array()),true);
$companyinfo = $v_data['data'];

$s_sql = "select * from contactperson where id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array(); 

$l_membersystem_id = $v_row[$v_membersystem_config['content_id_field']];
$s_receiver_name = $v_row['name'];
$s_receiver_email = $v_row['email'];

$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$_GET['companyID'], "USER"=>$s_receiver_email, "MEMBERSYSTEMID"=>$l_membersystem_id, 'COMPANYNAME_SELFDEFINED_ID'=>$_POST['selfdefined_company_id'])));
$l_access_id = $o_membersystem->data->id;

//$verification = json_decode(APIconnectAccount("userverificationgetwithoutgroup", $v_accountinfo['accountname'], $v_accountinfo['password'], array("USER"=>$s_receiver_email, "USERID"=>$l_access_id,"COMPANY_ID"=>$_GET['companyID'],"FULLNAME"=> $s_receiver_name)));
//$s_verification_url = $verification->url;

if(isset($_POST['selfdefined_company_id']))
{
	$s_response = APIconnectAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && 1 == $v_response['status'])
	{
		foreach($v_response['items'] as $v_item)
		{
			if($_POST['selfdefined_company_id'] == $v_item['id'] && $v_item['invitation_config'] != '')
			{
				$variables->invitation_config = $v_item['invitation_config'];
			}
		}
	}
}

$o_query = $o_main->db->query("SELECT * FROM customer_basisconfig");
$v_customer_config = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig");
if($o_query && $o_query->num_rows()>0)
{
	$v_customer_config = $o_query->row_array();
}
if($v_customer_config['activate_selfdefined_company'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE id = '".$o_main->db->escape_str($variables->invitation_config)."'");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_invitation_config = $o_query->row_array();
	}
} else {
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_customer_config['invitation_config'])."'");
	$v_invitation_config = $o_query ? $o_query->row_array() : array();
	$s_invitation_config = (isset($variables->invitation_config) && $variables->invitation_config != '') ? $variables->invitation_config : $v_customer_config['invitation_config'];
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($s_invitation_config)."'");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_invitation_config = $o_query->row_array();
	}
}
if($v_invitation_config['id'] > 0)
{
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	$fw_session = $o_query ? $o_query->row_array() : array();
	
	$v_logo = json_decode($v_invitation_config['getynet_logo'], TRUE);
	$v_partner_logo = json_decode($v_invitation_config['partner_logo'], TRUE);
	
	$s_logo = $s_partner_logo = '';
	$s_file = rawurldecode($v_logo[0][1][0]);
	if(is_file(__DIR__.'/../../../../'.$s_file))
	{
		$s_type = pathinfo(__DIR__.'/../../../../'.$s_file, PATHINFO_EXTENSION);
		$s_data = file_get_contents(__DIR__.'/../../../../'.$s_file);
		$s_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
	}
	$s_file = rawurldecode($v_partner_logo[0][1][0]);
	if(is_file(__DIR__.'/../../../../'.$s_file))
	{
		$s_type = pathinfo(__DIR__.'/../../../../'.$s_file, PATHINFO_EXTENSION);
		$s_data = file_get_contents(__DIR__.'/../../../../'.$s_file);
		$s_partner_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
	}
	
	$b_update_customer = TRUE;
	
	$v_param = array(
		'COMPANYACCESS_ID' => $l_access_id,
		'EMAIL' => $s_receiver_email,
		'FIRST_NAME' => $v_row['name'],
		'MIDDLE_NAME' => $v_row['middlename'],
		'LAST_NAME' => $v_row['lastname'],
		'MOBILE_PREFIX' => '',
		'MOBILE' => $v_row['mobile'],
		'INVITATION_TEXT' => $v_invitation_config['text'],
		'SENDER_FROM_NAME' => $v_invitation_config['sender_from_name'],
		'SENDER_FROM_EMAIL' => $v_invitation_config['sender_from_email'],
		'SHOW_SENDER_PERSON_IN_FOOTER' => $v_invitation_config['show_sender_person_in_footer'],
		'COMPANY_NAME' => $v_invitation_config['company_name'],
		'PARTNER_LOGO' => $s_partner_logo,
		'GETYNET_LOGO' => $s_logo,
		'VERIFY_MOBILE' => $v_invitation_config['ask_for_mobile_verification'],
		'INVITATION_LINK_BASE' => $v_invitation_config['register_here_url'],
		'LOGIN_PARTNER_CODE' => $v_invitation_config['login_partner_code'],
		'LANGUAGE_ID' => $fw_session['accountlanguageID']
	);
	$invitationresponse = json_decode(APIconnectUser('send_invitation_v2', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);
	
	if(isset($invitationresponse['status']) && 1 == $invitationresponse['status'])
	{
		$b_invitation_sent = true;
	} else {
		$b_invitation_sent = false;
	}
} else {
	$b_invitation_sent = false;
}
?>
<div class="popupform">
	<div class="popupfromTitle"><?php echo $formText_ResendInvitation_Output;?></div>
	<div><?php
	if($b_invitation_sent)
	{
		$response = json_decode(APIconnectAccount("sendinvitationdateset", $v_accountinfo['accountname'], $v_accountinfo['password'], array("USERID"=>$l_access_id)));
		echo $formText_InvitatinIsSent_Output;
	} else {
		echo $formText_ErrorOccuredSendingInvitation_Output;
	}
	echo $s_error_msg;
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