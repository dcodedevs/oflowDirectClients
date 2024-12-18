<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../input/includes/APIconnect.php");

$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$v_response = json_decode(APIconnectAccount('accountidcompanyidget', $v_accountinfo['accountname'], $v_accountinfo['password']), TRUE);

$l_company_id = (isset($v_response['data']) ? $v_response['data']['companyID'] : $v_data['company_id']);

$o_query = $o_main->db->query("SELECT * FROM customer_basisconfig");
$v_customer_config = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig");
if($o_query && $o_query->num_rows()>0)
{
	$v_customer_config = $o_query->row_array();
}
$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_customer_config['invitation_config'])."'");
$v_invitation_config = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($v_customer_config['invitation_config'])."'");
if($o_query && $o_query->num_rows()>0)
{
	$v_invitation_config = $o_query->row_array();
}
if($v_invitation_config['id'] > 0){
	$accesslevel = "";
	if($v_customer_config['invitation_accesslevel'] != "") {
		$accesslevel = $v_customer_config['invitation_accesslevel'];
		if($v_customer_config['invitation_groupID'] != "") {
			$groupId = $v_customer_config['invitation_groupID'];
		}
	}
	if($accesslevel != ""){
		$v_customers = array();
		$o_query = $o_main->db->query("SELECT * FROM contactperson WHERE email = '".$o_main->db->escape_str($v_data['params']['username'])."' AND customerId <> ''");
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			array_push($v_customers, $v_row['customerId']);
		}

		if(count($v_customers) > 0)
		{
			$v_customers_without_access = array();
			foreach($v_customers as $l_customer_id)
			{
				$response = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'],
					array("COMPANY_ID"=>$l_company_id, "USER"=>$v_data['params']['username'], "MEMBERSYSTEMID"=>$l_customer_id, "MEMBERSYSTEMMODULE"=>$v_customer_config['invitation_moduleName'])));
				if(!is_object($response->data)){
					array_push($v_customers_without_access, $l_customer_id);
				}
			}
			if(count($v_customers_without_access) > 0)
			{
				foreach($v_customers_without_access as $l_customer_id)
				{
					$o_query = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = '".$o_main->db->escape_str($l_customer_id)."' AND email = '".$o_main->db->escape_str($v_data['params']['username'])."'");
					if($o_query && $o_query->num_rows()>0)
					{
						$v_row = $o_query->row_array();
						$l_membersystem_id = $v_row[$v_customer_config['invitation_contentIdField']];
						$s_receiver_name = preg_replace('/\s+/', ' ', $v_row['name'].' '.$v_row['middlename'].' '.$v_row['lastname']);
						$s_receiver_email = $v_row['email'];
						$response = json_decode(APIconnectAccount("membersystemcustomersimplesetupdate", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$l_company_id, "USER"=>$s_receiver_email, "FULLNAME"=> $s_receiver_name, "MEMBERSYSTEMID"=>$l_membersystem_id, "GROUPID"=>$groupId, "MEMBERSYSTEMMODULENAME"=>$v_customer_config['invitation_moduleName'], "ACCESSLEVEL"=>$accesslevel)));

						if(isset($response->error))
						{
							$s_error_msg .= "<br><br>GetynetAPI: ".$response->error;
						} else {

							$companyAccessID = $response->data;

							$v_logo = json_decode($v_invitation_config['getynet_logo'], TRUE);
							$v_partner_logo = json_decode($v_invitation_config['partner_logo'], TRUE);

							$s_logo = $s_partner_logo = '';
							$s_file = $v_logo[0][1][0];
							if(is_file(__DIR__.'/../../../../'.$s_file))
							{
								$s_type = pathinfo(__DIR__.'/../../../../'.$s_file, PATHINFO_EXTENSION);
								$s_data = file_get_contents(__DIR__.'/../../../../'.$s_file);
								$s_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
							}
							$s_file = $v_partner_logo[0][1][0];
							if(is_file(__DIR__.'/../../../../'.$s_file))
							{
								$s_type = pathinfo(__DIR__.'/../../../../'.$s_file, PATHINFO_EXTENSION);
								$s_data = file_get_contents(__DIR__.'/../../../../'.$s_file);
								$s_partner_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
							}

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
								'LANGUAGE_ID' => $v_data['account_language_id']
							);
							$invitationresponse = json_decode(APIconnectAccount('send_invitation_v2', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param), TRUE);

							if(isset($invitationresponse['status']) && 1 == $invitationresponse['status'])
							{
								$v_return['status'] = 1;
							} else {
								$v_return['message'] = "-4";
							}
						}
					}
				}
				$v_return['message'] = 'cool';
			} else {
				$v_return['message'] = "-3";
			}
		} else {
			$v_return['message'] = "-2";
		}
	}  else {
		$v_return['message'] = "-6";
	}
}  else {
	$v_return['message'] = "-5";
}
