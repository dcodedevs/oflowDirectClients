<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

$l_customer_id = $v_data['params']['customer_id'];
$s_name = $v_data['params']['contactperson_name'];
$s_email = $v_data['params']['contactperson_email'];
$s_mobile = $v_data['params']['contactperson_mobile'];
$l_admin = intval($v_data['params']['contactperson_admin']);

if(isset($v_data['params']['token']))
{
	$s_username = 'WEB_TOKEN';
	$s_token = $v_data['params']['token'];
	$o_query = $o_main->db->query("SELECT id FROM customer WHERE id = '".$o_main->db->escape_str($l_customer_id)."' AND user_registration_token = '".$o_main->db->escape_str($s_token)."' AND user_registration >= 1");
} else {
	$s_username = $v_data['params']['username'];
	$o_query = $o_main->db->query("SELECT id FROM contactperson WHERE (email = '".$o_main->db->escape_str($s_username)."' AND customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (inactive IS NULL OR inactive < 1) AND admin = 1");
}
if($o_query && $o_query->num_rows()>0)
{
	$v_insert = array(
		'moduleID' => 41,
		'createdBy' => $s_username,
		'created' => date('Y-m-d H:i:s'),
		'origId' => 0,
		'sortNr' => 0,
		'customerId' => $l_customer_id,
		'name' => $s_name,
		'title' => '',
		'directPhone' => '',
		'mobile' => $s_mobile,
		'displayInMemberpage' => 0,
		'mainContact' => 0,
		'wantToReceiveInfo' => 0,
		'email' => $s_email,
		'notes' => '',
		'admin' => $l_admin,
		'inactive' => 0,
		'not_receive_messages' => 0,
		'access_card_number' => '',
		'external_locksystem_person_id' => '',
		'external_locksystem_pin' => '',
		'network_password' => '',
		'network_group_access' => '',
		'network_status' => 0
	);
	$o_query = $o_main->db->insert('contactperson', $v_insert);
	$l_contactperson_id = $o_main->db->insert_id();
	if($o_query)
	{
		$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0) $customer_basisconfig = $o_query->row_array();
		
		if($customer_basisconfig['activateContactPersonAccess'])
		{
			$s_email_template = "sendemail_standard";
			if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
			if(!function_exists("sendEmail_extract_images")) include_once(__DIR__."/../../input/includes/fn_sendEmail_extract_images.php");
			
			$s_sql = "select * from customer_stdmembersystem_basisconfig";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0) $v_membersystem_config = $o_query->row_array(); 
			
			$s_sql = "select * from accountinfo";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array(); 
			
			if(trim($v_accountinfo['default_email_sender_email_address']) == "")
			{
				$v_return['message'] .= "<br><br>".$formText_SenderEmailAddressNeedsToTeDefinedForSendingInvitation_Output;
			} elseif(trim($v_accountinfo['domain']) == "")
			{
				$v_return['message'] .= "<br><br>".$formText_DomainNameNeedsToTeDefinedForSendingInvitation_Output;
			} else {
				$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array()),true);
				$companyinfo = $v_data['data'];
				
				$s_sql = "select * from contactperson where id = ?";
				$o_query = $o_main->db->query($s_sql, array($l_contactperson_id));
				if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array(); 
			
				$l_membersystem_id = $v_row[$v_membersystem_config['content_id_field']];
				$s_receiver_name = $v_row['name'];
				$s_receiver_email = $v_row['email'];
				
				$response = json_decode(APIconnectAccount("membersystemcustomersimplesetupdatenogroup", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyinfo['id'],"USER"=>$s_receiver_email,"FULLNAME"=> $s_receiver_name,"MEMBERSYSTEMID"=>$l_membersystem_id,"GROUPID"=>$v_membersystem_config['group_id_or_name'],"MEMBERSYSTEMMODULENAME"=>'Customer2',"ACCESSLEVEL"=>$v_membersystem_config['access_level'])));
				
				if(isset($response->error))
				{
					$v_return['message'] .= "<br><br>GetynetAPI: ".$response->error;
				} else {
					$l_access_id = $response->data;
					$verification = json_decode(APIconnectAccount("userverificationgetwithoutgroup", $v_accountinfo['accountname'], $v_accountinfo['password'], array("USER"=>$s_receiver_email, "USERID"=>$l_access_id,"COMPANY_ID"=>$companyinfo['id'],"FULLNAME"=> $s_receiver_name)));
					
					$s_verification_url = $verification->url;
					if(isset($verification->error))
					{
						$v_return['message'] .= "<br><br>GetynetAPI: ".$verification->error;
					} else {
						$_POST["folder"] = $s_email_template;
						include(__DIR__."/../../output/includes/readOutputLanguage.php");
						include(__DIR__."/../../".$s_email_template."/template.php");
						
						$i=0;
						$imgAttach = array();
						$r_array = array('accounts/'.$v_accountinfo['accountname'].'/','/accounts/'.$v_accountinfo['accountname'].'/');
						$imgReplace = sendEmail_extract_images($s_email_body);
						foreach($imgReplace as $image)
						{
							if(is_file(__DIR__.'/../../../../'.str_replace($r_array,'',$image)))
							{
								$s_email_body = str_replace($image, 'cid:img'.$i, $s_email_body);
								$imgAttach[] = $image;
								$i++;
							}
						}
			
						$s_sql = "select * from sys_emailserverconfig order by default_server desc";
						$o_query = $o_main->db->query($s_sql);
						if($o_query && $o_query->num_rows()>0) $v_email_server_config = $o_query->row_array(); 
						
						$mail = new PHPMailer;
						$mail->CharSet	= 'UTF-8';
						$mail->IsSMTP(true);
						$mail->isHTML(true);
						if($v_email_server_config['host'] != "")
						{
							$mail->Host	= $v_email_server_config['host'];
							if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
							
							if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
							{
								$mail->SMTPAuth	= true;
								$mail->Username	= $v_email_server_config['username'];
								$mail->Password	= $v_email_server_config['password'];
						
							}
						} else {
							$mail->Host = "mail.dcode.no";
						}
						$mail->From		= $v_accountinfo['default_email_sender_email_address'];
						$mail->FromName	= $v_accountinfo['default_email_sender_name'];
						$mail->Subject  = $s_email_subject;
						$mail->Body		= $s_email_body;
						$mail->AddAddress($s_receiver_email);
						foreach($imgAttach as $key => $attach)
						{
							$mail->AddEmbeddedImage(__DIR__.'/../../../../'.str_replace($r_array,'',$attach), 'img'.$key);
						}
						if($mail->Send())
						{
							$response = json_decode(APIconnectAccount("sendinvitationdateset", $v_accountinfo['accountname'], $v_accountinfo['password'], array("USERID"=>$l_access_id)));
							if(isset($response->error))
							{
								$v_return['message'] .= "<br><br>GetynetAPI: ".$response->error;
							}
						}
					}
				}
				 
				$resultuserinfo = json_decode(APIconnectAccount("userinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'],array("COMPANY_ID"=>$companyinfo['id'],"SEARCH_USERNAME"=>$s_receiver_email)),true);
				if(isset($resultuserinfo->error))
				{
					$v_return['message'] .= "<br><br>GetynetAPI: ".$resultuserinfo->error;
				}
				if(is_array($resultuserinfo['data']))
					$userID = $resultuserinfo['data']['userID'];
				else
					$userID = ''; 
				
				$response = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyinfo['id'], "USER"=>$s_receiver_email, "MEMBERSYSTEMID"=>$l_membersystem_id, "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
				if(isset($response->error))
				{
					$v_return['message'] .= "<br><br>GetynetAPI: ".$response->error;
				}
				if(is_array($resultuserinfo['data']))
					$isregistered = 1; 
				else
					$isregistered = 0;
				
				if(is_object($response->data))
					$isactivated = 1;
				else
					$isactivated = 0;
				
				if($isregistered && $isactivated)
				{
					$v_return['status'] = 1;
				}
			}
		} else {
			$v_return['status'] = 1;
		}
	}
} else {
	$v_return['message'] = 'Admin access required';
}
