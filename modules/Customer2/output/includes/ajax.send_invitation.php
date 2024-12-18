<?php
$s_error_msg = "";
$result_text = "";
$s_email_template = "sendemail_standard";
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
// if(!function_exists("sendEmail_extract_images")) include_once(__DIR__."/../../input/includes/fn_sendEmail_extract_images.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();

?>
<div class="popupform">
	<div class="popupfromTitle"><?php echo $formText_ApproveInvitation_Output;?></div>
	<div>
<?php
	$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array()),true);
	$companyinfo = $v_data['data'];

	$peopleIds = $_POST['peopleIds'];
	$inbatch = $_POST['inbatch'];
	$peopleToInvite = array();
	if($inbatch){
		$peopleIdsArray = explode("&", $_POST['peopleIds']);
		foreach($peopleIdsArray as $peopleIdArray) {
			$peopleItem = explode("=", $peopleIdArray, 2);
			array_push($peopleToInvite, $peopleItem[1]);
		}
	} else {
		array_push($peopleToInvite, $_POST['cid']);
	}

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
					$variables->invitation_config_accesslevel = $v_item['invitation_config_accesslevel'];
					$variables->invitation_config_groupID = $v_item['invitation_config_groupID'];
					$variables->invitation_config_contentIdField = $v_item['invitation_config_contentIdField'];
					$variables->invitation_config_moduleName = $v_item['invitation_config_moduleName'];
				}
			}
		}
	}

	$o_query = $o_main->db->query("SELECT * FROM customer_basisconfig");
	$v_customer_basisconfig = $o_query ? $o_query->row_array() : array();
	$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig");
	$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

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
		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_customer_basisconfig['invitation_config'])."'");
		$v_invitation_config = $o_query ? $o_query->row_array() : array();

		if($v_customer_accountconfig['invitation_config'] != ""){
			$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_customer_accountconfig['invitation_config'])."'");
			$v_invitation_config = $o_query ? $o_query->row_array() : array();
			$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($v_customer_accountconfig['invitation_config'])."'");
			if($o_query && $o_query->num_rows()>0)
			{
				$v_invitation_config = $o_query->row_array();
			}
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
		$accesslevel = "";
		if($v_customer_config['invitation_accesslevel'] != "") {
			$accesslevel = $v_customer_config['invitation_accesslevel'];
			if($v_customer_config['invitation_groupID'] != "") {
				$groupId = $v_customer_config['invitation_groupID'];
			}
		}
		if($variables->invitation_config_accesslevel > 0) {
			$accesslevel = $variables->invitation_config_accesslevel;
		}
		if($variables->invitation_config_groupID != "") {
			$groupId = $variables->invitation_config_groupID;
		}

		if($accesslevel != ""){
			foreach($peopleToInvite as $peopleId)
			{
				$s_error_msg = "";
				$s_sql = "select * from contactperson where id = ?";
				$o_query = $o_main->db->query($s_sql, array($peopleId));
				if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
				$isregistered = 0;
				if($v_row){
					$s_sql = "UPDATE contactperson SET intranet_membership_type = ?, intranet_membership_subscription_type = ? where id = ?";
					$o_query = $o_main->db->query($s_sql, array(intval($_POST['intranet_membership_type']), intval($_POST['intranet_membership_subscription_type']), $peopleId));

					$s_sql = "DELETE intranet_membership_contactperson_connection FROM intranet_membership_contactperson_connection WHERE contactperson_id = ?";
					$o_query = $o_main->db->query($s_sql, array($peopleId));

					$s_sql = "DELETE contactperson_subscription_connection FROM contactperson_subscription_connection WHERE contactperson_id = ?";
					$o_query = $o_main->db->query($s_sql, array($peopleId));

					if($_POST['intranet_membership_type'] == 1){
						foreach($_POST['intranet_membership_connections'] as $intranet_membership_connection) {
							$s_sql = "INSERT INTO intranet_membership_contactperson_connection SET contactperson_id = ?, membership_id = ?";
							$o_query = $o_main->db->query($s_sql, array($peopleId, $intranet_membership_connection));
						}
					}
					if($_POST['intranet_membership_subscription_type'] == 1){
						foreach($_POST['intranet_membership_subscription_connections'] as $intranet_membership_subscription_connection) {
							$s_sql = "INSERT INTO contactperson_subscription_connection SET contactperson_id = ?, subscriptionmulti_id = ?";
							$o_query = $o_main->db->query($s_sql, array($peopleId, $intranet_membership_subscription_connection));
						}
					}

					if($b_update_customer)
					{
						$o_main->db->query("UPDATE customer SET selfdefined_company_id = '".$o_main->db->escape_str($_POST['selfdefined_company_id'])."' WHERE id = '".$o_main->db->escape_str($v_row['customerId'])."' AND (selfdefined_company_id = 0 OR selfdefined_company_id IS NULL)");
						$b_update_customer = FALSE;
					}
					$contentIdField = $v_customer_config['invitation_contentIdField'];
					$invitationModuleName = $v_customer_config['invitation_moduleName'];

					if($variables->invitation_config_contentIdField != "") {
						$contentIdField = $variables->invitation_config_contentIdField;
					}
					if($variables->invitation_config_moduleName != "") {
						$invitationModuleName = $variables->invitation_config_moduleName;
					}
					$l_membersystem_id = $v_row[$contentIdField];
					$s_receiver_name = preg_replace('/\s+/', ' ', $v_row['name'].' '.$v_row['middlename'].' '.$v_row['lastname']);
					$s_receiver_email = $v_row['email'];
					//membersystemcustomersimplesetupdate
					$response = json_decode(APIconnectAccount("membersystemcustomersimplesetupdatenogroup", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$_GET['companyID'],"USER"=>$s_receiver_email,"FULLNAME"=> $s_receiver_name,"MEMBERSYSTEMID"=>$l_membersystem_id,"GROUPID"=>$groupId,"MEMBERSYSTEMMODULENAME"=>$invitationModuleName,"ACCESSLEVEL"=>$accesslevel, 'COMPANYNAME_SELFDEFINED_ID'=>$_POST['selfdefined_company_id'])));

					if(isset($response->error))
					{
						$s_error_msg = "GetynetAPI: ".$response->error;
					} else {

						if(!isset($_POST['change_access']))
						{
							$companyAccessID = $response->data;

							$v_param = array(
								'COMPANYACCESS_ID' => $companyAccessID,
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
								$isregistered = 1;
							} else {
								$s_error_msg = $invitationresponse['error'];
							}
						} else {
							$isregistered = 1;
						}
					}

					if($isregistered == 1)
					{
						$result_text .= $formText_AccessGranted_Output." - ".$v_row['email']."</br>";
					} else {
						$result_text .=  $formText_ErrorOccured_Output." - ".$v_row['email']." ".$s_error_msg."</br>";
					}
				}
			}
		} else {
			$isregistered = false;
			$result_text .= "<br/>".$formText_AccessLevelWasNotSetInConfig_output;
		}
	} else {
		$result_text .= "<br><br>".$formText_InvitationConfigurationError_Output;
	}
?>
<?php  echo $result_text;?>
</div>
</div>
<div class="popupformbtn">
	<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
</div>
<style>
.popupform {
	border: 0;
}
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
