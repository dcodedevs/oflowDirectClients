<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once("Exception.php");
require_once("PHPMailer.php");
require_once("SMTP.php");

if(!function_exists("generateRandomString")){
	function generateRandomString($length = 8) {
	    $characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}

$s_sql = "SELECT * FROM collecting_system_settings";
$o_query = $o_main->db->query($s_sql);
$collecting_system_settings = $o_query ? $o_query->row_array() : array();

$casesToGenerate = $_POST['casesToGenerate'];

$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND (sending_status is null OR sending_status = 0 OR sending_status = 2)";
$o_query = $o_main->db->query($s_sql);
$cases = $o_query ? $o_query->result_array() : array();

if(count($cases) > 0){
	$created_letters = 0;
	foreach($cases as $created_letter)
	{
		if(in_array($created_letter['id'], $casesToGenerate)) {
			$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), sending_status = -2 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			$o_query = $o_main->db->query($s_sql);

			// $sendEmail = false;
			// if($created_letter['case_id'] > 0){
			// 	$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
			// 	$o_query = $o_main->db->query($s_sql, array($created_letter['case_id']));
			// 	$case = $o_query ? $o_query->row_array() : array();
			//
			// 	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
			// 	$o_query = $o_main->db->query($s_sql, array($created_letter['step_id']));
			// 	$process_step = ($o_query ? $o_query->row_array() : array());
			// } else {
			// 	$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
			// 	$o_query = $o_main->db->query($s_sql, array($created_letter['collecting_company_case_id']));
			// 	$case = $o_query ? $o_query->row_array() : array();
			//
			// 	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
			// 	$o_query = $o_main->db->query($s_sql, array($created_letter['step_id']));
			// 	$process_step = ($o_query ? $o_query->row_array() : array());
			// }
			// if($case){
			//
            //     $s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
            //     $o_query = $o_main->db->query($s_sql, array($case['id']));
            //     $creditor_invoice = ($o_query ? $o_query->row_array() : array());
			//
			// 	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			// 	$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
			// 	$creditor = ($o_query ? $o_query->row_array() : array());
			//
			// 	$s_sql = "SELECT *, concat_ws(' ',customer.name, customer.middlename, customer.lastname) as fullName FROM customer WHERE customer.id = ?";
			// 	$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
			// 	$debitor = ($o_query ? $o_query->row_array() : array());
			//
			// 	$companyPhone = $creditor['companyphone'];
			// 	$companyEmail = $creditor['companyEmail'];
			// 	if($creditor['use_local_email_phone_for_reminder']) {
			// 		$companyPhone = $creditor['local_phone'];
			// 		$companyEmail = $creditor['local_email'];
			// 	}
			//
			// 	if($created_letter['sending_action'] == 2) {
			// 		if($debitor && preg_replace('/\xc2\xa0/', '', trim($debitor['invoiceEmail'])) != "" && $creditor) {
			// 			$sendEmail = true;
			// 		}
			// 	}
			// 	if($sendEmail) {
			// 		if(file_exists(__DIR__."/../../../../".$created_letter['pdf'])){
			// 			$s_email_subject = $formText_ReminderFrom_output." ".$creditor['companyname'];
			//
			// 			$s_email_body = $formText_Hi_pdf." ".$debitor['fullName']."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['companyname']."<br/>";
			// 			if($companyPhone != "") {
			// 				$s_email_body .= $formText_Phone_pdf." ".$companyPhone;
			// 			}
			// 			$s_email_body.="<br/><br/>".$formText_ThisEmailSentFromReminderSystemOflow_output." (<a href='".$formText_realWebAddressAtBottomOfEmail_output."'>".$formText_realWebAddressAtBottomOfEmail_output."</a>)";
			//
			//
			// 			$s_sql = "select * from sys_emailserverconfig order by default_server desc";
			// 			$o_query = $o_main->db->query($s_sql);
			// 			$v_email_server_config = $o_query ? $o_query->row_array() : array();
			//
			// 			$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'collecting_cases_claim_letter', '', 0, ?, ?, ?);";
			// 			$o_main->db->query($s_sql, array($creditor['sender_name'], $creditor['sender_email'], $created_letter['id'], $s_email_subject, $s_email_body, $batch_id));
			// 			$l_emailsend_id = $o_main->db->insert_id();
			// 			$invoiceEmail_string = str_replace(",",";",preg_replace('/\xc2\xa0/', '', trim($debitor['invoiceEmail'])));
			// 			$invoiceEmails = explode(";", $invoiceEmail_string);
			//
			// 				// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
			// 				//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
			// 				// Trim rest spaces and new lines
			// 				//$invoiceEmail = trim($invoiceEmail);
			// 			if(count($invoiceEmails) > 0){
			// 				$mail = new PHPMailer;
			// 				$mail->CharSet	= 'UTF-8';
			// 				$mail->IsSMTP(true);
			// 				$mail->isHTML(true);
			// 				if($v_email_server_config['host'] != "")
			// 				{
			// 					$mail->Host	= $v_email_server_config['host'];
			// 					if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
			//
			// 					if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
			// 					{
			// 						$mail->SMTPAuth	= true;
			// 						$mail->Username	= $v_email_server_config['username'];
			// 						$mail->Password	= $v_email_server_config['password'];
			//
			// 					}
			// 				} else {
			// 					$mail->Host = "mail.dcode.no";
			// 				}
			// 				if($companyEmail != "") {
			// 					$mail->addReplyTo($companyEmail, $creditor['companyname']);
			// 				}
			// 				$mail->From		= $collecting_system_settings['reminder_sender_email'];
			// 				$mail->FromName	= $creditor['companyname'];
			// 				$mail->Subject	= $s_email_subject;
			// 				$mail->Body		= $s_email_body;
			// 				$emailAdded = false;
			// 				foreach($invoiceEmails as $invoiceEmail){
			// 					if(filter_var(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)), FILTER_VALIDATE_EMAIL))
			// 					{
			// 						$emailAdded = true;
			// 						$mail->AddAddress(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)));
			// 					}
			// 				}
			// 				// $mail->AddBCC("david@dcode.no");
			// 				$mail->AddAttachment(__DIR__."/../../../../".$created_letter['pdf']);
			// 				if($emailAdded){
			// 					$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
			// 					$o_main->db->query($s_sql, array($l_emailsend_id, $creditor['companyname'], preg_replace('/\xc2\xa0/', '', trim($invoiceEmail))));
			// 					$l_emailsendto_id = $o_main->db->insert_id();
			//
			// 					if($mail->Send())
			// 					{
			// 						$emails_sent++;
			// 						$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 1, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 			    		$o_query = $o_main->db->query($s_sql);
			// 						$created_letters[] = $created_letter;
			// 					} else {
			// 						$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message ='".$o_main->db->escape_str($mail->ErrorInfo)."' WHERE id = '".$o_main->db->escape_str($l_emailsendto_id)."'";
			// 			    		$o_query = $o_main->db->query($s_sql);
			// 					}
			// 				}
			// 			}
			// 		}
			// 	} else if($created_letter['sending_action'] == 1) {
			// 		$l_send_status = 0;
			// 		do{
			// 			$code = generateRandomString(10);
			// 			$code_check = null;
			// 			$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
			// 			$o_query = $o_main->db->query($s_sql, array($code));
			// 			if($o_query){
			// 				$code_check = $o_query->row_array();
			// 			}
			// 		} while($code_check != null);
			//
	        //         $s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), print_batch_code = ?, sent_to_external_company = 2 WHERE id = ?";
	        //         $o_query = $o_main->db->query($s_sql, array($code, $created_letter['id']));
			// 		$file_name_with_full_path = $created_letter['pdf'];
			// 		if(file_exists(__DIR__."/../../../../".$file_name_with_full_path)){
			// 			$web_page_to_send = "https://min.bypost.no/dokumentsenter/mottak/4da0dd29-eb56-ee11-be6f-000d3ad8fd72";//"https://pitofficeupload.bypost.no/caa2d405-2591-4f8c-8bc3-db7e4ae2dabe";
			//
			// 			$post_request = array(
			// 				"file" => curl_file_create(__DIR__."/../../../../".$file_name_with_full_path, "application/pdf",basename($file_name_with_full_path)) // for php 5.5+
			// 			);
			// 			$ch = curl_init();
			// 			curl_setopt($ch, CURLOPT_URL, $web_page_to_send);
			// 			curl_setopt($ch, CURLOPT_POST, 1);
			// 			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
			// 			$result = curl_exec($ch);
			// 			$http_status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			// 			curl_close($ch);
			// 			if($http_status == 200){
			// 				$l_send_status = 1;
			// 				$created_letters[] = $created_letter;
			// 			}
			//
		    //             // $hook_file = __DIR__ . '/../../../IntegrationJCloud/hooks/send_print_file.php';
			// 			// $hook_result = array();
		    // 	        // if (file_exists($hook_file)) {
		    // 	        //     require $hook_file;
		    // 	        //     if (is_callable($run_hook)) {
		    // 			// 		$hook_params = array(
		    // 			// 			'letter_id' => $created_letter['id']
		    // 			// 		);
		    // 			// 		$hook_result = $run_hook($hook_params);
			// 			// 		// var_dump($hook_result);
			// 			// 		if($hook_result['result'] == ""){
			// 			// 			$l_send_status = 1;
			// 			// 			$created_letters[] = $created_letter;
			// 			// 		}
		    // 	        //     }
		    // 			// 	unset($run_hook);
		    // 	        // }
			//
			// 			if($l_send_status == 1){
			// 				$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 1, sent_to_external_company = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 				$o_query = $o_main->db->query($s_sql);
			// 			} else {
			// 				$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str(json_encode($http_status))."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 				$o_query = $o_main->db->query($s_sql);
			// 			}
			// 		}
			// 	} else if($created_letter['sending_action'] == 3) {
			// 		$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 2, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			//
			// 		$o_query = $o_main->db->query($s_sql);
			// 		$created_letters[] = $created_letter;
			// 	} else if($created_letter['sending_action'] == 4) {
			//
			// 		$o_query = $o_main->db->query("SELECT * FROM sys_smsserviceconfig ORDER BY default_config DESC");
			// 		$v_sms_service_config = $o_query ? $o_query->row_array() : array();
			//
			// 		if($v_sms_service_config["username"] == "" || $v_sms_service_config["password"] == "")
			// 		{
			// 			$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("sms sending not configured")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 			$o_query = $o_main->db->query($s_sql);
			// 		} else {
			// 			// $s_sql = "SELECT * FROM collecting_cases_smstext WHERE collecting_cases_smstext.id = ?";
			// 			// $o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_smstext_id']));
			// 			// $sms_text = ($o_query ? $o_query->row_array() : array());
			//
			// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE collecting_cases_process_step_id = ? AND creditor_reminder_custom_profile_id = ?";
			// 			$o_query = $o_main->db->query($s_sql, array($process_step['id'], $case['reminder_profile_id']));
			// 			$step_profile_value = ($o_query ? $o_query->row_array() : array());
			// 			$extra_sms_text = "";
			// 			if($step_profile_value){
			// 				$extra_sms_text = $step_profile_value['extra_text_in_sms'];
			// 			}
			// 			$totalAmount = $created_letter['total_amount'];
			// 			$invoiceNumber = $creditor_invoice['invoice_nr'];
			// 			$bankaccount_nr = $creditor['bank_account'];
			// 			$kidNumber = $creditor_invoice['kid_number'];
			// 			$company_name = $creditor['companyname'];
			// 			$smsMessage = "Hei ".$debitor['fullName']."! Har du glemt oss? ".$extra_sms_text." Det gjenstår kr ".number_format($totalAmount, 2, ",", "")." å betale på faktura ".$invoiceNumber.". Vennligst betal til kontonr ".$bankaccount_nr." med KID ".$kidNumber." omgående. Om ikke kan ekstra kostnader påløpe. Mvh ".$company_name;
			// 			if($smsMessage != ""){
			// 				$l_sms_failed = 0;
			// 				$l_sms_success = 0;
			// 				$s_send_on = date("d-m-Y H:i");
			// 				$l_send_type = 2;
			//
			// 				$s_secure = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
			// 				list($s_protocol,$s_rest) = explode("/", strtolower($_SERVER["SERVER_PROTOCOL"]),2);
			// 				$l_port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
			// 				$s_account_url = $s_protocol.$s_secure."://".$_SERVER['SERVER_NAME'].$l_port."/accounts/".$_GET['accountname']."/";
			//
			// 				if($creditor['sms_sendername'] !=""){
			// 					$s_sender = preg_replace('#[^A-za-z0-9]+#', '', $creditor['sms_sendername']);
			// 				} else {
			// 					$s_sender = preg_replace('#[^A-za-z0-9]+#', '', $creditor['companyname']);
			// 				}
			// 				if(!is_numeric($s_sender) && strlen($s_sender) > 11) $s_sender = substr($s_sender, 0, 11);
			//
			// 				$sql = "INSERT INTO sys_smssend (id, created, createdBy, `type`, send_on, sender, sender_email, content_module_id, content_id, content_table, message)
			// 				VALUES (NULL, NOW(), '".$o_main->db->escape_str($variables->loggID)."', '".$o_main->db->escape_str($l_send_type)."', STR_TO_DATE('".$o_main->db->escape_str($s_send_on)."','%d-%m-%Y %H:%i'), '".$o_main->db->escape_str($s_sender)."', '".$o_main->db->escape_str($variables->loggID)."', '".$o_main->db->escape_str($moduleID)."', '".$o_main->db->escape_str($created_letter['id'])."', 'collecting_cases_claim_letter', '".$o_main->db->escape_str($smsMessage)."');";
			// 				$o_main->db->query($sql);
			// 				$l_smssend_id = $o_main->db->insert_id();
			// 				if($l_smssend_id > 0){
			//
			// 					if(strpos($debitor['phone'],'+')===false)
			// 						$debitor['phone'] = $v_sms_service_config['prefix'].$debitor['phone'];
			//
			// 					$sql = "INSERT INTO sys_smssendto
			// 							(id, smssend_id, receiver, receiver_mobile, extra1, extra2, `status`, status_message, response, perform_time, perform_count)
			// 							VALUES (NULL, ?, ?, ?, ?, ?, 0, '', '', '', 0)";
			// 					$o_main->db->query($sql, array($l_smssend_id, $debitor['name'], $debitor['phone'], $debitor['extra1'], $debitor['extra2']));
			// 					$l_smssendto_id = $o_main->db->insert_id();
			//
			// 					$b_sms_sent = false;
			// 					if(isset($v_sms_service_config['service_id']) && $v_sms_service_config['service_id'] != "")
			// 					{
			// 						$v_param = array(
			// 							'source' => $s_sender,
			// 							'destination' => $debitor['phone'],
			// 							'userData' => $smsMessage,
			// 							'platformId' => "COMMON_API",
			// 							'platformPartnerId' => $v_sms_service_config['service_id'],
			// 							'refId' => base64_encode(json_encode(array('accountname' => $_GET['accountname'], 'sendto_id' => $l_smssendto_id, 'platformPartnerId' => $v_sms_service_config['service_id'])))
			// 						);
			// 						if(isset($v_sms_service_config['gate_id']) && '' != $v_sms_service_config['gate_id'])
			// 						{
			// 							$v_param['useDeliveryReport'] = TRUE;
			// 							$v_param['deliveryReportGates'] = array($v_sms_service_config['gate_id']);
			// 						}
			// 						$s_param = json_encode($v_param);
			// 						$o_curl  = curl_init();
			// 						curl_setopt($o_curl, CURLOPT_URL, 'https://wsx.sp247.net/sms/send');
			// 						curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, TRUE);
			// 						curl_setopt($o_curl, CURLOPT_POSTFIELDS, $s_param);
			// 						curl_setopt($o_curl, CURLOPT_USERPWD, $v_sms_service_config['username'] . ":" . $v_sms_service_config['password']);
			// 						curl_setopt($o_curl, CURLOPT_HTTPHEADER, array(
			// 							'Content-Type: application/json',
			// 							'Content-Length: ' . strlen($s_param)
			// 						));
			//
			// 						$s_response = curl_exec($o_curl);
			// 						$v_response = json_decode($s_response, TRUE);
			// 						curl_close($o_curl);
			//
			// 						$b_sms_sent = ($v_response['resultCode'] == '1005');
			// 						$l_sending_status = 1;
			// 					} else {
			// 						$v_param = array('User' => $v_sms_service_config['username'], 'Password' => $v_sms_service_config['password'],
			// 								'LookupOption' => $v_sms_service_config['lookup_option'], 'MessageType' => $v_sms_service_config['type'],
			// 								'Originator' => $s_sender, 'RequireAck' => 1, 'AckUrl' => $s_account_url."elementsGlobal/smsack.php",
			// 								'BatchID' => $l_smssendto_id, 'ChannelID' => 0, 'Msisdn' => $debitor['phone'], 'Data' => $smsMessage);
			//
			// 						//call api
			// 						$s_url = 'http://msgw.linkmobility.com/MessageService.aspx';
			//
			// 						$ch = curl_init();
			// 						curl_setopt($ch, CURLOPT_URL, $s_url.'?'.http_build_query($v_param));
			// 						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//
			// 						$s_response = curl_exec($ch);
			// 						curl_close($ch);
			//
			// 						$b_sms_sent = (strpos($s_response,'NOK')===false);
			// 						$l_sending_status = 1;
			// 					}
			// 					if($b_sms_sent)
			// 					{
			// 						$l_sms_success++;
			// 						$o_main->db->query("update sys_smssendto set status = ?, response = ?, perform_time = NOW(), perform_count = 1 where id = ? and status = 0", array($l_sending_status, $s_response, $l_smssendto_id));
			// 						$created_letters[] = $created_letter;
			//
			// 						$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 4, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 						$o_query = $o_main->db->query($s_sql);
			// 					} else {
			// 						$l_sms_failed++;
			// 						$o_main->db->query("update sys_smssendto set status = 3, status_message = 'Error occured on sms registration', response = ?, perform_time = NOW(), perform_count = 1 where id = ? and status = 0", array($s_response, $l_smssendto_id));
			//
			// 						$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("failed to send")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 						$o_query = $o_main->db->query($s_sql);
			// 					}
			// 				} else {
			// 					$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("failed to create sending info in database")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 					$o_query = $o_main->db->query($s_sql);
			// 				}
			// 			} else {
			// 				$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("missing sms text")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			// 				$o_query = $o_main->db->query($s_sql);
			// 			}
			// 		}
			// 	}
			// }
		}
	}
}

if($created_letters > 0){
	$fw_return_data = array(
		'status' => 1,
		'batch_id' => $batch_id,
	);
} else {
	$fw_error_msg[] = $formText_NoPdfCreated_output;
}
