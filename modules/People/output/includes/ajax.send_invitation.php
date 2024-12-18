<?php
if($accessElementAllow_GiveRemoveAccessPeople || isset($_POST['from_owncompany']))
{
	$s_error_msg = "";
	$s_email_template = "sendemail_standard";
	if(!class_exists("PHPMailer")) require("PHPMailerAutoload.php");
	if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
	if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");
	if(!function_exists("sendEmail_extract_images")) include_once(__DIR__."/../../input/includes/fn_sendEmail_extract_images.php");

	$o_query = $o_main->db->query("SELECT * FROM people_basisconfig");
	$v_people_config = $o_query ? $o_query->row_array() : array();
	$o_query = $o_main->db->query("SELECT * FROM people_accountconfig");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_people_config = $o_query->row_array();
	}

	if(intval($v_employee_basisconfig['invitation_setting']) == 1){
		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_employee_basisconfig['invitation_config'])."'");
		$v_invitation_config = $o_query ? $o_query->row_array() : array();

		if($v_employee_accountconfig['invitation_config'] != ""){
			$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_employee_accountconfig['invitation_config'])."'");
			$v_invitation_config = $o_query ? $o_query->row_array() : array();
			$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($v_employee_accountconfig['invitation_config'])."'");
			if($o_query && $o_query->num_rows()>0)
			{
				$v_invitation_config = $o_query->row_array();
			}
		}
	}
	if(isset($_POST['from_owncompany']) && '' != $v_people_config['personowncompany_invitation_config'])
	{
		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($v_people_config['personowncompany_invitation_config'])."'");
		$v_invitation_config = $o_query ? $o_query->row_array() : array();
	}
	$linkedToCrmAccount = false;
	if($v_employee_accountconfig['linked_crm_account'] != "" && $v_employee_accountconfig['linked_crm_account_token'] != ""){
		$linkedToCrmAccount = true;
	}
	$result_text = "";
	if($v_invitation_config['id'] || $linkedToCrmAccount || intval($v_employee_basisconfig['invitation_setting']) == 0){
		if($linkedToCrmAccount){
			$crm_customer_id = $_POST['crm_customer_id'];
			$isregistered = false;
			if($crm_customer_id > 0) {

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

				$params = array(
					'api_url' => $v_employee_accountconfig['linked_crm_account'].'/api',
					'access_token'=> $v_employee_accountconfig['linked_crm_account_token'],
					'module' => 'Customer2',
					'action' => 'send_invitation_from_owncompany',
					'params' => array(
						'customerId' => $crm_customer_id,
						'contactpersonIds' => $peopleToInvite,
						'caID' => $_GET['caID'],
						'companyID' => $_GET['companyID'],
						'username' => $_COOKIE['username'],
						'sessionID' => $_COOKIE['sessionID']
					)
				);
				$response = fw_api_call($params, false);
				if($response['status'] == 1) {
					$isregistered = true;
				}
			}
			if($isregistered == 1)
			{
				$result_text .= $formText_AccessGranted_Output."<br/>";
			} else {
				$result_text .= $formText_ErrorOccured_Output."<br/>";
			}
		} else {
			if($v_invitation_config){
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
			}

			$v_accountinfo_sql = $o_main->db->query("select * from accountinfo");
			if($v_accountinfo_sql && $v_accountinfo_sql->num_rows() > 0) $v_accountinfo = $v_accountinfo_sql->row();

			$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo->accountname, $v_accountinfo->password, array()),true);
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

			$accesslevel = "";
			$memberSystemAccess = false;
			if($_POST['from_owncompany']) {
				if($v_people_config['personowncompany_fixed_contentIdField'] != ""){
					$memberSystemAccess = true;
					if($v_people_config['personowncompany_fixed_accesslevel'] != "") {
						$accesslevel = $v_people_config['personowncompany_fixed_accesslevel'];
						if($v_people_config['personowncompany_fixed_groupID'] != "") {
							$groupId = $v_people_config['personowncompany_fixed_groupID'];
						}
					}
					$contentIdField = $v_people_config['personowncompany_fixed_contentIdField'];
					$invitationModuleName = $v_people_config['personowncompany_fixed_moduleName'];
				} else {
					if($v_employee_accountconfig['personowncompany_fixed_accesslevel'] != "" && $v_employee_accountconfig['personowncompany_fixed_groupID'] != "") {
						$accesslevel = $v_employee_accountconfig['personowncompany_fixed_accesslevel'];
						$accesslevel .= "_".$v_employee_accountconfig['personowncompany_fixed_groupID'];
					}
				}
			} else {
				if($v_employee_basisconfig['invitation_select_access'] || $v_employee_accountconfig['invitation_select_access']  || intval($v_employee_basisconfig['invitation_setting']) == 0){
					$accesslevel = $_POST['accesslevel'];
				} else {
					if($v_people_config['invitation_contentIdField'] != ""){
						$memberSystemAccess = true;
						if($v_people_config['invitation_accesslevel'] != "") {
							$accesslevel = $v_people_config['invitation_accesslevel'];
							if($v_people_config['invitation_groupID'] != "") {
								$groupId = $v_people_config['invitation_groupID'];
							}
						}
						$contentIdField = $v_people_config['invitation_contentIdField'];
						$invitationModuleName = $v_people_config['invitation_moduleName'];
					} else {
						if($v_employee_basisconfig['invitation_accesslevel'] != "") {
							$accesslevel = $v_employee_basisconfig['invitation_accesslevel'];
							if($v_employee_basisconfig['invitation_groupID'] != "") {
								$accesslevel .= "_".$v_employee_basisconfig['invitation_groupID'];
							}
						}
						if($v_employee_accountconfig['invitation_accesslevel'] != "") {
							$accesslevel = $v_employee_accountconfig['invitation_accesslevel'];
							if($v_employee_accountconfig['invitation_groupID'] != "") {
								$accesslevel .= "_".$v_employee_accountconfig['invitation_groupID'];
							}
						}
					}
				}
			}
			if($accesslevel != "" || $_POST['resend']){
				foreach($peopleToInvite as $peopleId){
					$companyAccessID = "";
					$s_error_msg = "";
					$s_sql = "select * from contactperson where id = ?";
					$o_result = $o_main->db->query($s_sql, array($peopleId));
					$v_row = $o_result ? $o_result->row_array() : array();
					$l_employee_id = $v_row['id'];
					$s_receiver_email = trim($v_row['email']);
					if($s_receiver_email != ""){
						if($memberSystemAccess){
							$l_membersystem_id = $v_row[$contentIdField];
							$s_receiver_name = preg_replace('/\s+/', ' ', $v_row['name'].' '.$v_row['middlename'].' '.$v_row['lastname']);

							if($invitationModuleName == "Customer2"){
								$s_sql = "SELECT * FROM people_crm_contactperson_connection WHERE people_id = ? AND (notVisibleInMemberOverview = 0 OR notVisibleInMemberOverview is null)";
				                $o_result = $o_main->db->query($s_sql, array($v_row['id']));
				                $crm_connections = $o_result ? $o_result->result_array() : array();
				                foreach($crm_connections as $crm_connection) {
									$l_membersystem_id = $crm_connection['crm_customer_id'];
								}
							}
							//membersystemcustomersimplesetupdate
							$response = json_decode(APIconnectAccount("membersystemcustomersimplesetupdatenogroup", $v_accountinfo->accountname, $v_accountinfo->password, array("COMPANY_ID"=>$_GET['companyID'],"USER"=>$s_receiver_email,"FULLNAME"=> $s_receiver_name,"MEMBERSYSTEMID"=>$l_membersystem_id,
							"GROUPID"=>$groupId,"MEMBERSYSTEMMODULENAME"=>$invitationModuleName,"ACCESSLEVEL"=>$accesslevel, 'COMPANYNAME_SELFDEFINED_ID'=>$_POST['selfdefined_company_id'])));

							if(isset($response->error))
							{
								$isregistered = false;
								$s_error_msg = "".$response->error;
							} else {
								$companyAccessID = $response->data;
							}
						} else {
							$data = json_decode(APIconnectorUser("companyaccessget",$variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'], 'USERNAME'=>$s_receiver_email, 'ACCESSID'=>$_GET['accessID'])),true);
							$userAccess = $data['data'];
							if($userAccess['id'] != null){
								$newUserID = $userAccess['id'];
							}
							if($_POST['resend']){
								$_POST['admin'] = $userAccess['admin'];
								$accesslevel = $userAccess['accesslevel'];
								$groupId = $userAccess['groupID'];
								if($groupId > 0){
									$accesslevel .= "_".$groupId;
								}
							}
							$s_sql = $o_main->db->query("SELECT * FROM sys_modulemenuusers WHERE username = ?", array($s_receiver_email));
							if($s_sql->num_rows() == 0){
								$o_main->db->query("insert into sys_modulemenuusers(set_id, username) values('".$o_main->db->escape($_POST['modulemenuset'])."', '".$o_main->db->escape($s_receiver_email)."')");
							}

							$v_specific_access = array();
							while(list($key,$value) = each($_POST))
							{
								//module access
								if(stristr($key,"account_"))
								{
									list($r,$accountID,$r,$moduleID) = explode("_",$key);
									//$v_specific_access[$accountID][$moduleID] = $value;
									$tmp = 0;
									foreach($_POST[$key] as $val) $tmp += intval($val);
									$v_specific_access[$accountID][$moduleID] = $tmp;
								}
								if(stristr($key,"accountaccess_"))
								{
									list($r,$accountID) = explode("_",$key);
									$v_specific_access[$accountID]['current'] = $value;
								}
								//content access
								if(stristr($key,"content_"))
								{
									list($rest, $l_access_id, $moduleID, $accountID) = explode("_",$key);
									$v_specific_access[$accountID]['content'][$moduleID][$l_access_id] = intval($_POST[$key]);
								}
								//extended content access
								if(stristr($key,"extended_"))
								{
									list($rest, $l_access_id, $moduleID, $accountID) = explode("_",$key);
									$v_extended_access[] = array('account_id'=>$accountID, 'module_id'=>$moduleID, 'access_id'=>$l_access_id, 'accesslevel'=>intval($_POST[$key]));
								}
								//accesselements
								if(stristr($key,"accesselement_"))
								{
									list($r,$accountID,$r,$moduleID) = explode("_",$key);
									$v_specific_access[$accountID]['accesselement'][$moduleID]['allow'] = $_POST[$key];
								}
								if(stristr($key,"accesselementrestrict_"))
								{
									list($r,$accountID,$r,$moduleID) = explode("_",$key);
									$v_specific_access[$accountID]['accesselement'][$moduleID]['restrict'] = $_POST[$key];
								}
							}
							$resultcompanyaccess = json_decode(APIconnectorAccount("companyaccessset", $v_accountinfo->accountname, $v_accountinfo->password,
								array(
								'COMPANY_ID'=>$_GET['companyID'],
								'COMPANYACCESS_ID'=>$newUserID,
								'FIRST_NAME'=>$v_row['name'],
								'MIDDLE_NAME'=>$v_row['middle_name'],
								'LAST_NAME'=>$v_row['last_name'],
								'USERNAME'=>$s_receiver_email,
								'ADMIN'=>$_POST['admin'],
								'SYSTEM_ADMIN'=>$userAccess['system_admin'],
								'DEVELOPERACCESS'=>$userAccess['developeraccess'],
								'ACCESSLEVEL'=>$accesslevel,
								'DEACTIVATED'=>0,
								'SPECIFICACCESS'=>$v_specific_access,
								'ACCESSID'=>$_GET['accessID'],
								'MOBILE'=>$v_row['mobile']
								)),true);
							if(isset($resultcompanyaccess['status']) && $resultcompanyaccess['status'] == 1)
							{
								$companyAccessID = $resultcompanyaccess['data'];
							} else {
								$isregistered = false;
								$s_error_msg = "".$resultcompanyaccess['error'];
							}
						}

						if($companyAccessID > 0) {
							$data = json_decode(APIconnectorUser("departmentaccessdelete", $variables->loggID, $variables->sessionID, array('COMPANYACCESS_ID'=>$companyAccessID,'COMPANYDEPARTMENT_ID'=>'')),true);
							reset($_POST);
							while(list($key,$value) = each($_POST))
							{//echo "key =$key<br />";
								if(stristr($key,"departmentcheck_"))
								{
									list($r,$departmentID) = explode("_",$key);
								 	//echo "departmentID = $departmentID<br />";
									$data = json_decode(APIconnectorUser("departmentaccessadd", $variables->loggID, $variables->sessionID, array('COMPANYACCESS_ID'=>$companyAccessID,'COMPANYDEPARTMENT_ID'=>$departmentID)),true);
								}

								if(stristr($key,"departmentsetALL_"))
								{
									list($r,$departmentsetID) = explode("_",$key);
								 	//echo "departmentID = $departmentID<br />";
									$data = json_decode(APIconnectorUser("departmentaccessaddset", $variables->loggID, $variables->sessionID, array('COMPANYACCESS_ID'=>$companyAccessID,'COMPANYDEPARTMENTSETIDALL'=>$departmentsetID)),true);
								}
							}

							$isregistered = true;
							if(isset($_POST['sendInvitation']))
							{
								$v_param = array(
									'COMPANYACCESS_ID' => $companyAccessID,
									'EMAIL' => $s_receiver_email,
									'FIRST_NAME' => $v_row['name'],
									'MIDDLE_NAME' => $v_row['middle_name'],
									'LAST_NAME' => $v_row['last_name'],
									'MOBILE_PREFIX' => $v_row['phone_prefix'],
									'MOBILE' => $v_row['phone'],
									'INVITATION_TEXT' => nl2br($v_invitation_config['text']),
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

								$invitationresponse = json_decode(APIconnectorUser('send_invitation_v2', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), true);

								if(isset($invitationresponse['error']))
								{
									$isregistered = false;
									$s_error_msg = $invitationresponse['error'];
								}
							}
						}
					} else {
						$isregistered = false;
					}
					if($isregistered == 1)
					{
						$result_text .= $formText_AccessGranted_Output."<br/>";
					} else {
						$result_text .= $formText_ErrorOccured_Output." ".$s_error_msg."<br/>";
					}
				}
			} else {
				$isregistered = false;
				$result_text .= $formText_ErrorOccured_Output." ".$formText_AccessLevelWasNotSetInConfig_output;
			}
		}
	} else {
		$isregistered = false;
		$result_text .= $formText_ErrorOccured_Output." ".$formText_NoInvitationConfigFound_output;
	}
	if(!isset($_POST['hide_output'])){
	?>
		<div class="popupform">
			<div class="popupfromTitle"><?php echo $formText_ApproveInvitation_Output;?></div>
			<div><?php
			echo $result_text;
			?></div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
		</div>
		<style>
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
	<?php } ?>
<?php } else {
	echo $formText_YouHaveNoAccess_Output;
}
