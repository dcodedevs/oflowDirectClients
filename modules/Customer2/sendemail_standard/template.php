<?php
/*
 * TEMPLATE STANDARD
 *
 * Add images
 * $s_email_body .= "<img src=\"accounts/".$accountname."/".$image ."\" border=\"0\" align=\"right\" hspace=\"20\" style=\"padding-left:20px;\">";
*/
$_POST["folder"] = "sendemail_standard";
include(__DIR__."/../output/includes/readOutputLanguage.php");
$s_link = "https://www.getynet.com/";
$s_display_link = "www.getynet.com";

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
	if(!$variables->invitation_config){
		$l_selfdefined_company_id = $customer['selfdefined_company_id'];

		$s_sql = "select * from accountinfo";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
		    $v_accountinfo = $o_query->row_array();
		}

		$v_selfdefined_companies = array();
		// $b_activate_selfdefined_company = $b_check_selfdefined_company = FALSE;
		$s_response = APIconnectAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			// $b_activate_selfdefined_company = TRUE;
			$v_selfdefined_companies = $v_response['items'];
		}
		foreach($v_selfdefined_companies as $v_item)
		{
			if($l_selfdefined_company_id == $v_item['id'] && $v_item['invitation_config'] != '')
			{
				$variables->invitation_config = $v_item['invitation_config'];
			}
		}
	}

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
	$v_logo = json_decode($v_invitation_config['getynet_logo'], TRUE);
	$v_partner_logo = json_decode($v_invitation_config['partner_logo'], TRUE);

	$s_logo = $s_partner_logo = '';
	$s_file = rawurldecode($v_logo[0][1][0]);
	if(is_file(__DIR__.'/../../../'.$s_file))
	{
		$s_type = pathinfo(__DIR__.'/../../../'.$s_file, PATHINFO_EXTENSION);
		$s_data = file_get_contents(__DIR__.'/../../../'.$s_file);
		$s_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
	}
	$s_file = rawurldecode($v_partner_logo[0][1][0]);
	if(is_file(__DIR__.'/../../../'.$s_file))
	{
		$s_type = pathinfo(__DIR__.'/../../../'.$s_file, PATHINFO_EXTENSION);
		$s_data = file_get_contents(__DIR__.'/../../../'.$s_file);
		$s_partner_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
	}
	$v_param = array(
		'COMPANY_ID' => $companyID,
		'EMAIL' => $s_receiver_email,
		'FIRST_NAME' => $v_contactperson['name'],
		'MIDDLE_NAME' => $v_contactperson['middlename'],
		'LAST_NAME' => $v_contactperson['lastname'],
		'INVITATION_TEXT' => $v_invitation_config['text'],
		'SENDER_FROM_NAME' => $v_invitation_config['sender_from_name'],
		'SENDER_FROM_EMAIL' => $v_invitation_config['sender_from_email'],
		'SHOW_SENDER_PERSON_IN_FOOTER' => $v_invitation_config['show_sender_person_in_footer'],
		'COMPANY_NAME' => $v_invitation_config['company_name'],
		'PARTNER_LOGO' => $s_partner_logo,
		'GETYNET_LOGO' => $s_logo,
		'VERIFY_MOBILE' => '',
		'INVITATION_LINK_BASE' => $v_invitation_config['register_here_url'],
		'LOGIN_PARTNER_CODE' => $v_invitation_config['login_partner_code'],
		'LANGUAGE_ID' => $fw_session['accountlanguageID']
	);
	$invitationresponse = json_decode(APIconnectorUser('send_invitation_v2_preview', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), true);

	if($invitationresponse['status']){
		$s_email_from = $invitationresponse['email_from'];
		$s_email_body = $invitationresponse['email_body'];
		$s_email_subject = $invitationresponse['email_subject'];
	} else {
		$s_email_body = $invitationresponse['error'];
	}
} else {
	// $s_email_body = $formText_InvitationConfigurationError_Output;
}
