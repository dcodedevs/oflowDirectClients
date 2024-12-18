<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
ob_start();
$v_return = array(
	'status' => 0,
	'messages' => array(),
);
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

define('BASEPATH', realpath(__DIR__.'/../../../').'/');
require_once(BASEPATH.'elementsGlobal/cMain.php');
include_once(__DIR__."/includes/readOutputLanguage.php");

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
$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{

	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);

	$s_sql = "SELECT ccl.*, IF(ccs.id is null, cred.companyname, cred_cc.companyname) as creditorName FROM collecting_cases_claim_letter ccl
	LEFT OUTER JOIN collecting_cases cs ON cs.id = ccl.case_id
	LEFT OUTER JOIN collecting_company_cases ccs ON ccs.id = ccl.collecting_company_case_id
	LEFT OUTER JOIN creditor cred ON cred.id = cs.creditor_id
	LEFT OUTER JOIN creditor cred_cc ON cred_cc.id = ccs.creditor_id
	WHERE ccl.content_status < 2 AND ccl.sending_status = -2 ORDER BY ccl.sending_action ASC, ccl.created ASC LIMIT 40";
	$o_query = $o_main->db->query($s_sql);
	$cases = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM collecting_system_settings";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = $o_query ? $o_query->row_array() : array();
	
	$s_sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($s_sql);
	$ownercompany = $o_query ? $o_query->row_array() : array();

	if(count($cases) > 0){

		include_once(__DIR__."/../output/languagesOutput/no.php");
		$created_letters = 0;
		foreach($cases as $created_letter)
		{
			try {
				$time_log = array();
				$time_log[] = ' script started '.microtime();
				$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), sending_status = -1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$sending_error = "";
				if(strpos(mb_strtolower($created_letter['creditorName']), "(demo)") === false){
					if($created_letter['sending_action'] > 0) {
						if($created_letter['pdf'] != "" && file_exists(__DIR__."/../../../".$created_letter['pdf'])) {
							if($created_letter['total_amount'] > 0) {
								if($created_letter['due_date'] == "0000-00-00" || $created_letter['due_date'] == "" || $created_letter['due_date'] == "1970-01-01") {
									$sending_error = "Due date is missing";
								} else {
									$sendEmail = false;
									$b_send_ehf = FALSE;
									if($created_letter['case_id'] > 0) {
										$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($created_letter['case_id']));
										$case = $o_query ? $o_query->row_array() : array();

										$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($created_letter['step_id']));
										$process_step = ($o_query ? $o_query->row_array() : array());
									} else {
										$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($created_letter['collecting_company_case_id']));
										$case = $o_query ? $o_query->row_array() : array();

										$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($created_letter['step_id']));
										$process_step = ($o_query ? $o_query->row_array() : array());
									}
									if($case){
										$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
										$o_query = $o_main->db->query($s_sql, array($case['id']));
										$creditor_invoice = ($o_query ? $o_query->row_array() : array());

										$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
										$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
										$creditor = ($o_query ? $o_query->row_array() : array());

										$s_sql = "SELECT *, concat_ws(' ',customer.name, customer.middlename, customer.lastname) as fullName FROM customer WHERE customer.id = ?";
										$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
										$debitor = ($o_query ? $o_query->row_array() : array());

										$companyPhone = $creditor['companyphone'];
										$companyEmail = $creditor['companyEmail'];
										if($creditor['use_local_email_phone_for_reminder']) {
											$companyPhone = $creditor['local_phone'];
											$companyEmail = $creditor['local_email'];
										}
										$invoiceEmail = "";
										if($created_letter['collecting_company_case_id'] > 0) {
											if($created_letter['sending_action'] == 2) {
												if($debitor && preg_replace('/\xc2\xa0/', '', trim($debitor['extra_invoice_email'])) != "" && $creditor) {
													$sendEmail = true;
													$invoiceEmail = $debitor['extra_invoice_email'];
												}
											}
										} else {
											if($created_letter['sending_action'] == 2) {
												if($debitor && preg_replace('/\xc2\xa0/', '', trim($debitor['invoiceEmail'])) != "" && $creditor) {
													$sendEmail = true;
													$invoiceEmail = $debitor['invoiceEmail'];
												}
											}
										}
										if($created_letter['sending_action'] == 5) {
											$b_send_ehf = TRUE;
										}
										if($sendEmail) {
											if(file_exists(__DIR__."/../../../".$created_letter['pdf'])){
												
												if($created_letter['collecting_company_case_id'] > 0) {
													$s_sender_email = "post@oflow.no";
													$s_sender_name = "Oflow AS";
													$s_email_subject = "Brev fra Oflow AS - Saksnr ".$created_letter['collecting_company_case_id'];
													$s_email_body = "Filen er sendt fra Oflow AS";
													
												} else {
													$s_sender_email = $collecting_system_settings['reminder_sender_email'];
													$s_sender_name = $creditor['companyname'];
													$s_email_subject = $formText_ReminderFrom_output." ".$creditor['companyname'];

													$s_email_body = $formText_Hi_pdf." ".$debitor['fullName']."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['companyname']."<br/>";
													if($companyPhone != "") {
														$s_email_body .= $formText_Phone_pdf." ".$companyPhone;
													}
													$s_email_body.="<br/><br/>".$formText_ThisEmailSentFromReminderSystemOflow_output." (<a href='".$formText_realWebAddressAtBottomOfEmail_output."'>".$formText_realWebAddressAtBottomOfEmail_output."</a>)";

												}

												$s_sql = "select * from sys_emailserverconfig order by default_server desc";
												$o_query = $o_main->db->query($s_sql);
												$v_email_server_config = $o_query ? $o_query->row_array() : array();

												$s_sql = "INSERT INTO sys_emailsend 
												(id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) 
												VALUES (NULL, NOW(), ?, 2, NOW(), ?, ?, 0, 0, ?, 'collecting_cases_claim_letter', '', 0, ?, ?, ?);";
												$o_main->db->query($s_sql, array($companyEmail, $creditor['companyname'], $collecting_system_settings['reminder_sender_email'], $created_letter['id'], $s_email_subject, $s_email_body, $batch_id));
												$l_emailsend_id = $o_main->db->insert_id();
												$invoiceEmail_string = str_replace(",",";",preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)));
												$invoiceEmails = explode(";", $invoiceEmail_string);

													// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
													//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
													// Trim rest spaces and new lines
													//$invoiceEmail = trim($invoiceEmail);
												if(count($invoiceEmails) > 0){
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
															if($v_email_server_config['host'] == "mail3.getynetmail.com"){
																$mail->SMTPSecure = 'ssl';
															}

														}
													} else {
														$mail->Host = "mail.dcode.no";
													}
													if($companyEmail != "") {
														$mail->addReplyTo($companyEmail, $creditor['companyname']);
													}
													$mail->From		= $s_sender_email;
													$mail->FromName	= $s_sender_name;
													$mail->Subject	= $s_email_subject;
													$mail->Body		= $s_email_body;
													
													$emailAdded = false;
													foreach($invoiceEmails as $invoiceEmail){
														if(filter_var(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)), FILTER_VALIDATE_EMAIL))
														{
															$emailAdded = true;
															$mail->AddAddress(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)));
														}
													}
													$mail->AddBCC("david@dcode.no");
													// $mail->AddBCC("byamba@dcode.no");
													$mail->AddAttachment(__DIR__."/../../../".$created_letter['pdf']);
													if($emailAdded){
														$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
														$o_main->db->query($s_sql, array($l_emailsend_id, $creditor['companyname'], preg_replace('/\xc2\xa0/', '', trim($invoiceEmail))));
														$l_emailsendto_id = $o_main->db->insert_id();

														if($mail->Send())
														{
															$emails_sent++;
															$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 1, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
															$o_query = $o_main->db->query($s_sql);
															$created_letters[] = $created_letter;
														} else {
															$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message ='".$o_main->db->escape_str($mail->ErrorInfo)."' WHERE id = '".$o_main->db->escape_str($l_emailsendto_id)."'";
															$o_query = $o_main->db->query($s_sql);
														}
													} else {
														$sending_error = "invalid email";
													}
												}
											}
										} else if($created_letter['sending_action'] == 1) {
											$l_send_status = 0;
											do{
												$code = generateRandomString(10);
												$code_check = null;
												$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
												$o_query = $o_main->db->query($s_sql, array($code));
												if($o_query){
													$code_check = $o_query->row_array();
												}
											} while($code_check != null);

											$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), print_batch_code = ?, sent_to_external_company = 2 WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($code, $created_letter['id']));
											$file_name_with_full_path = $created_letter['pdf'];
											if(file_exists(__DIR__."/../../../".$file_name_with_full_path)){
												$web_page_to_send = "https://min.bypost.no/dokumentsenter/mottak/4da0dd29-eb56-ee11-be6f-000d3ad8fd72"; //"https://pitofficeupload.bypost.no/caa2d405-2591-4f8c-8bc3-db7e4ae2dabe";

												$post_request = array(
													"file" => curl_file_create(__DIR__."/../../../".$file_name_with_full_path, "application/pdf",basename($file_name_with_full_path)) // for php 5.5+
												);
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $web_page_to_send);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
												$result = curl_exec($ch);
												$http_status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
												curl_close($ch);
												if($http_status == 200){
													$l_send_status = 1;
													$created_letters[] = $created_letter;
												}

												if($l_send_status == 1){
													$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 1, sent_to_external_company = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
													$o_query = $o_main->db->query($s_sql);
												} else {
													$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str(json_encode($http_status))."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
													$o_query = $o_main->db->query($s_sql);
												}
											}
										} else if($created_letter['sending_action'] == 3) {
											$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 2, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";

											$o_query = $o_main->db->query($s_sql);
											$created_letters[] = $created_letter;
										} else if($created_letter['sending_action'] == 4) {

											$o_query = $o_main->db->query("SELECT * FROM sys_smsserviceconfig ORDER BY default_config DESC");
											$v_sms_service_config = $o_query ? $o_query->row_array() : array();

											if($v_sms_service_config["username"] == "" || $v_sms_service_config["password"] == "")
											{
												$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("sms sending not configured")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
												$o_query = $o_main->db->query($s_sql);
											} else {
												// $s_sql = "SELECT * FROM collecting_cases_smstext WHERE collecting_cases_smstext.id = ?";
												// $o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_smstext_id']));
												// $sms_text = ($o_query ? $o_query->row_array() : array());

												$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE collecting_cases_process_step_id = ? AND creditor_reminder_custom_profile_id = ?";
												$o_query = $o_main->db->query($s_sql, array($process_step['id'], $case['reminder_profile_id']));
												$step_profile_value = ($o_query ? $o_query->row_array() : array());
												$extra_sms_text = "";
												if($step_profile_value){
													$extra_sms_text = $step_profile_value['extra_text_in_sms'];
												}
												$totalAmount = $created_letter['total_amount'];
												$invoiceNumber = $creditor_invoice['invoice_nr'];
												$bankaccount_nr = $creditor['bank_account'];
												$kidNumber = $creditor_invoice['kid_number'];
												$company_name = $creditor['companyname'];
												$smsMessage = "Hei ".$debitor['fullName']."! Har du glemt oss? ".$extra_sms_text." Det gjenstår kr ".number_format($totalAmount, 2, ",", "")." å betale på faktura ".$invoiceNumber.". Vennligst betal til kontonr ".$bankaccount_nr." med KID ".$kidNumber." omgående. Om ikke kan ekstra kostnader påløpe. Mvh ".$company_name;
												if($smsMessage != ""){
													$l_sms_failed = 0;
													$l_sms_success = 0;
													$s_send_on = date("d-m-Y H:i");
													$l_send_type = 2;

													$s_secure = empty($_SERVER["HTTPS"]) ? '' : (($_SERVER["HTTPS"] == "on") ? "s" : "");
													list($s_protocol,$s_rest) = explode("/", strtolower($_SERVER["SERVER_PROTOCOL"]),2);
													$l_port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
													$s_account_url = $s_protocol.$s_secure."://".$_SERVER['SERVER_NAME'].$l_port."/accounts/".$_GET['accountname']."/";

													if($creditor['sms_sendername'] !=""){
														$s_sender = preg_replace('#[^A-za-z0-9]+#', '', $creditor['sms_sendername']);
													} else {
														$s_sender = preg_replace('#[^A-za-z0-9]+#', '', $creditor['companyname']);
													}
													if(!is_numeric($s_sender) && strlen($s_sender) > 11) $s_sender = substr($s_sender, 0, 11);

													$sql = "INSERT INTO sys_smssend (id, created, createdBy, `type`, send_on, sender, sender_email, content_module_id, content_id, content_table, message)
													VALUES (NULL, NOW(), '".$o_main->db->escape_str($variables->loggID)."', '".$o_main->db->escape_str($l_send_type)."', STR_TO_DATE('".$o_main->db->escape_str($s_send_on)."','%d-%m-%Y %H:%i'), '".$o_main->db->escape_str($s_sender)."', '".$o_main->db->escape_str($variables->loggID)."', '".$o_main->db->escape_str($moduleID)."', '".$o_main->db->escape_str($created_letter['id'])."', 'collecting_cases_claim_letter', '".$o_main->db->escape_str($smsMessage)."');";
													$o_main->db->query($sql);
													$l_smssend_id = $o_main->db->insert_id();
													if($l_smssend_id > 0){

														if(strpos($debitor['phone'],'+')===false)
															$debitor['phone'] = $v_sms_service_config['prefix'].$debitor['phone'];

														$sql = "INSERT INTO sys_smssendto
																(id, smssend_id, receiver, receiver_mobile, extra1, extra2, `status`, status_message, response, perform_time, perform_count)
																VALUES (NULL, ?, ?, ?, ?, ?, 0, '', '', '', 0)";
														$o_main->db->query($sql, array($l_smssend_id, $debitor['name'], $debitor['phone'], $debitor['extra1'], $debitor['extra2']));
														$l_smssendto_id = $o_main->db->insert_id();

														$b_sms_sent = false;
														if(isset($v_sms_service_config['service_id']) && $v_sms_service_config['service_id'] != "")
														{
															$v_param = array(
																'source' => $s_sender,
																'destination' => $debitor['phone'],
																'userData' => $smsMessage,
																'platformId' => "COMMON_API",
																'platformPartnerId' => $v_sms_service_config['service_id'],
																'refId' => base64_encode(json_encode(array('accountname' => $_GET['accountname'], 'sendto_id' => $l_smssendto_id, 'platformPartnerId' => $v_sms_service_config['service_id'])))
															);
															if(isset($v_sms_service_config['gate_id']) && '' != $v_sms_service_config['gate_id'])
															{
																$v_param['useDeliveryReport'] = TRUE;
																$v_param['deliveryReportGates'] = array($v_sms_service_config['gate_id']);
															}
															$s_param = json_encode($v_param);
															$o_curl  = curl_init();
															curl_setopt($o_curl, CURLOPT_URL, 'https://wsx.sp247.net/sms/send');
															curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, TRUE);
															curl_setopt($o_curl, CURLOPT_POSTFIELDS, $s_param);
															curl_setopt($o_curl, CURLOPT_USERPWD, $v_sms_service_config['username'] . ":" . $v_sms_service_config['password']);
															curl_setopt($o_curl, CURLOPT_HTTPHEADER, array(
																'Content-Type: application/json',
																'Content-Length: ' . strlen($s_param)
															));

															$s_response = curl_exec($o_curl);
															$v_response = json_decode($s_response, TRUE);
															curl_close($o_curl);

															$b_sms_sent = ($v_response['resultCode'] == '1005');
															$l_sending_status = 1;
														} else {
															$v_param = array('User' => $v_sms_service_config['username'], 'Password' => $v_sms_service_config['password'],
																	'LookupOption' => $v_sms_service_config['lookup_option'], 'MessageType' => $v_sms_service_config['type'],
																	'Originator' => $s_sender, 'RequireAck' => 1, 'AckUrl' => $s_account_url."elementsGlobal/smsack.php",
																	'BatchID' => $l_smssendto_id, 'ChannelID' => 0, 'Msisdn' => $debitor['phone'], 'Data' => $smsMessage);

															//call api
															$s_url = 'http://msgw.linkmobility.com/MessageService.aspx';

															$ch = curl_init();
															curl_setopt($ch, CURLOPT_URL, $s_url.'?'.http_build_query($v_param));
															curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

															$s_response = curl_exec($ch);
															curl_close($ch);

															$b_sms_sent = (strpos($s_response,'NOK')===false);
															$l_sending_status = 1;
														}
														if($b_sms_sent)
														{
															$l_sms_success++;

															$o_main->db->query("update sys_smssendto set status = ?, response = ?, perform_time = NOW(), perform_count = 1 where id = ? and status = 0", array($l_sending_status, $s_response, $l_smssendto_id));
															$created_letters[] = $created_letter;

															$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 4, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
															$o_query = $o_main->db->query($s_sql);
														} else {
															$l_sms_failed++;
															$o_main->db->query("update sys_smssendto set status = 3, status_message = 'Error occured on sms registration', response = ?, perform_time = NOW(), perform_count = 1 where id = ? and status = 0", array($s_response, $l_smssendto_id));

															$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("failed to send")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
															$o_query = $o_main->db->query($s_sql);
														}
													} else {
														$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("failed to create sending info in database")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
														$o_query = $o_main->db->query($s_sql);
													}
												} else {
													$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str("missing sms text")."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
													$o_query = $o_main->db->query($s_sql);
												}
											}
										} else if($created_letter['sending_action'] == 5)
										{
											$time_log[] = " start ehf ".microtime();
											if($created_letter['collecting_company_case_id'] > 0){
												$decimalPlaces = 2;
												$totalAmount = $created_letter['total_amount'];
												$invoiceNumber = $case['id'];
												$bankaccount_nr = $ownercompany['companyaccount'];
												$kidNumber = $case['kid_number'];
												
												$supplier_name = $ownercompany['companyname'];
												$supplier_street = $ownercompany['companypostalbox'];
												$supplier_city = $ownercompany['companypostalplace'];
												$supplier_postal_code = $ownercompany['companyzipcode'];
												$supplier_contact_id = $ownercompany['id'];
												$supplier_contact_phone = $ownercompany['companyphone'];
												$supplier_contact_email = $ownercompany['companyEmail'];
												$supplier_org_nr = $ownercompany['companyorgnr'];
											} else {
												$decimalPlaces = 2;
												$totalAmount = $created_letter['total_amount'];
												$invoiceNumber = $creditor_invoice['invoice_nr'];
												$bankaccount_nr = $creditor['bank_account'];
												$kidNumber = $creditor_invoice['kid_number'];
												$supplier_name = $creditor['companyname'];
												$supplier_street = $creditor['companypostalbox'];
												$supplier_city = $creditor['companypostalplace'];
												$supplier_postal_code = $creditor['companyzipcode'];
												$supplier_contact_id = $creditor['id'];
												$supplier_contact_phone = $creditor['companyphone'];
												$supplier_contact_email = $creditor['companyEmail'];
												$supplier_org_nr = $creditor['companyorgnr'];
											}
											// *************** EHF CREATION START ******
											$s_custom_error = '';
											$v_ehf_data['invoice_nr'] = $invoiceNumber;
											$v_ehf_data['invoice_issue_date'] = date("Y-m-d", strtotime($created_letter['created']));
											// 380 - Commercial invoice
											// 393 - Factored invoice
											// 384 - Corrected invoice
											// ftp://ftp.cen.eu/public/CWAs/BII2/CWA16558/CWA16558-Annex-G-BII-CodeLists-V2_0_4.pdf - page 15
											$v_ehf_data['invoice_type_code'] = 380;
											$v_ehf_data['invoice_note'] = ''; // optional
											$v_ehf_data['tax_point_date'] = ''; // optional - Y-m-d
											$v_ehf_data['currency_code'] = 'NOK';
											$v_ehf_data['accounting_cost'] = ''; // optional
											$v_ehf_data['period_start'] = ''; // optional - Y-m-d
											$v_ehf_data['period_end'] = ''; // optional - Y-m-d
											$v_ehf_data['order_reference'] = ''; // recommended - filled below
					
											$v_ehf_data['contract_document_reference'] = ''; // recommended - Contract321
											//$v_data['contract_document_type_code'] = '2'; // optional
											//$v_data['contract_document_type'] = 'Framework agreement'; // optional
					
											$v_ehf_data['supplier_org_nr'] = preg_replace('#[^0-9]+#', '', $supplier_org_nr);
											$v_ehf_data['supplier_identification'] = ''; // optional
											$v_ehf_data['supplier_name'] = $supplier_name;
											$v_ehf_data['supplier_street'] = $supplier_street; // optional
											$v_ehf_data['supplier_city'] = $supplier_city;
											$v_ehf_data['supplier_postal_code'] = $supplier_postal_code;
											$v_ehf_data['supplier_country'] = 'NO';
											$v_ehf_data['supplier_org_nr_vat'] = (stripos($v_ehf_data['supplier_org_nr'], 'NO') === FALSE ? 'NO' : '').$v_ehf_data['supplier_org_nr'].(stripos($v_ehf_data['supplier_org_nr'], 'MVA') === FALSE ? 'MVA' : ''); // optional (mandatory when zerro VAT) //TODO: NO + MVA
											$v_ehf_data['supplier_contact_id'] = $supplier_contact_id; // recommended
											//$v_ehf_data['supplier_contact_name'] = ''; // recommended
											$v_ehf_data['supplier_contact_phone'] = $supplier_contact_phone; // recommended
											//$v_ehf_data['supplier_contact_fax'] = ''; // recommended
											$v_ehf_data['supplier_contact_email'] = $supplier_contact_email; // recommended
											
											$s_cust_addr_prefix = 'pa';
											$o_contactperson = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ? ORDER BY mainContact DESC, id ASC", array($debitor['id']));
											$v_contactperson = $o_contactperson ? $o_contactperson->row_array() : array();
											$v_ehf_data['customer_org_nr'] = preg_replace('#[^0-9]+#', '', $debitor['publicRegisterId']);
											$v_ehf_data['customer_identification'] = $debitor['id']; // optional
											$v_ehf_data['customer_name'] = trim($debitor['name']." ".$debitor['middlename']." ".$debitor['lastname']);
											$v_ehf_data['customer_street'] = $debitor[$s_cust_addr_prefix.'Street']; // optional
											$v_ehf_data['customer_street_additional'] = $debitor[$s_cust_addr_prefix.'Street2']; // optional
											$v_ehf_data['customer_city'] = $debitor[$s_cust_addr_prefix.'City'];
											$v_ehf_data['customer_postal_code'] = $debitor[$s_cust_addr_prefix.'PostalNumber'];
											//$v_ehf_data['customer_country_subentity'] = ''; // optional
											$v_ehf_data['customer_country'] = strtoupper(''!=$debitor['country_code']?$debitor['country_code']:'NO');
											// Mandatory ONLY FOR COMPANIES (not for consumers) start
											$v_ehf_data['customer_org_nr_vat'] = $v_ehf_data['customer_country'].$v_ehf_data['customer_org_nr'].(('NO'==strtoupper($v_ehf_data['customer_country']) && stripos($v_ehf_data['customer_org_nr'], 'MVA') === FALSE) ? 'MVA' : '');
											$v_ehf_data['customer_legal_name'] = trim($debitor['name']." ".$debitor['middlename']." ".$debitor['lastname']);
											$v_ehf_data['customer_legal_org_nr'] = preg_replace('#[^0-9]+#', '', $debitor['publicRegisterId']);
											$v_ehf_data['customer_legal_city'] = $debitor[$s_cust_addr_prefix.'City']; // optional
											$v_ehf_data['customer_legal_country'] = strtoupper(''!=$debitor['country_code']?$debitor['country_code']:'NO'); // optional
											// Mandatory ONLY FOR COMPANIES (not for consumers) end
											$v_ehf_data['customer_contact_id'] = ($v_contactperson['id'] != '' ? $v_contactperson['id'] : 1); // Name or identifier specifying the customers reference (Eg employee number)
											$v_ehf_data['customer_contact_name'] = trim($v_contactperson['name']." ".$v_contactperson['middlename']." ".$v_contactperson['lastname']); // optional
											$v_ehf_data['customer_contact_phone'] = '';//($v_contactperson['directPhone']!=''?$v_contactperson['directPhone']:$v_contactperson['mobile']); // optional
											$v_ehf_data['customer_contact_fax'] = ''; // optional
											$v_ehf_data['customer_contact_email'] = '';//$v_contactperson['email']; // optional
					
											//Optional >>
											//$v_ehf_data['payee_identification'] = ''; // 2298740918237
											//$v_ehf_data['payee_name'] = ''; // Ebeneser Scrooge AS
											//$v_ehf_data['payee_company_id'] = ''; //999999999
											// << Optional
											//Optional >>
											//$v_ehf_data['tax_representative_name'] = ''; // Tax handling company AS
											//$v_ehf_data['tax_representative_street'] = '';
											//$v_ehf_data['tax_representative_street_additional'] = ''; // Front door
											//$v_ehf_data['tax_representative_city'] = '';
											//$v_ehf_data['tax_representative_postal_code'] = '';
											//$v_ehf_data['tax_representative_country_subentity'] = '';
											//$v_ehf_data['tax_representative_country'] = 'NO';
											//$v_ehf_data['tax_representative_tax_scheme_company_nr'] = ''; // 999999999MVA
											// << Optional
					
											$v_ehf_data['payment_means_code'] = '31'; //Code according to UN/CEFACT codelist 4461
											$v_ehf_data['payment_due_date'] = date("Y-m-d", strtotime($created_letter['due_date']));
											$v_ehf_data['payment_id'] = (0 != $kidNumber ? $kidNumber : ''); //In Norway this element is used for KID number.
											$v_ehf_data['payment_bank_account_type'] = 'BBAN'; //BBAN, IBAN
											$v_ehf_data['payment_bank_account'] = preg_replace('#[^A-Za-z0-9]+#', '', $bankaccount_nr); // $bankAccountData['companyiban']
											$v_ehf_data['payment_financial_institution_branch_id'] = 'BIC'; // Dependent
											$v_ehf_data['payment_financial_institution_bic'] = '';//$bankAccountData['companyswift'];  // Dependent
											//Optional >>
											$v_ehf_data['payment_terms'] = ''; //"2 % discount if paid within 2 days", "Penalty percentage 10% from due date";
											//Optional <<
											//Optional >>
											$v_ehf_data['allowance_charge'] = array();
											$v_item = array();
											$v_item['type'] = 'true'; //true = Charge, false = Allowance
											$v_item['reason_code'] = ''; // 94, Use codelist AllowanceChargeReasonCode, UN/ECE 4465, Version D08B
											$v_item['reason'] = ''; //packing charges
											$v_item['amount'] = ''; //100
											$v_item['tax_category'] = ''; //S
											$v_item['tax_percent'] = ''; //25
											//$v_ehf_data['allowance_charge'][] = $v_item;
											//Optional <<
											
											$v_ehf_data['order_reference'] = '';
					
											$v_ehf_data['tax_amount'] = round(0, $decimalPlaces);
											$v_ehf_data['tax_subtotal'] = array();
											
											$v_taxes = array(
												array(
													'taxable_amount' => round(floatval($totalAmount), $decimalPlaces),
													'tax_amount' => round(0, $decimalPlaces),
													'tax_percent' => 0,
													'tax_category' => 'E',
													'reason' => 'Momsfritak (0%)',
												)
											);
											foreach($v_taxes as $v_tax)
											{
												$v_item = array();
												$v_item['taxable_amount'] = round($v_tax['taxable_amount'], $decimalPlaces);
												$v_item['tax_amount'] = round($v_tax['tax_amount'], $decimalPlaces);
												$v_item['tax_percent'] = $v_tax['tax_percent'];
												$v_item['tax_category'] = $v_tax['tax_category'];
												// Depend
												if(isset($v_tax['reason']))
												$v_item['tax_exemption_reason'] = $v_tax['reason']; //Exempt New Means of Transport = Mandatory if VAT category = E
												$v_ehf_data['tax_subtotal'][] = $v_item;
											}
					
											$v_ehf_data['legal_monetary_line_extension'] = round(floatval($totalAmount), $decimalPlaces);//round(floatval($ordersArray['totals']['totalSum']), $decimalPlaces);
											$v_ehf_data['legal_monetary_tax_exclusive'] = round(floatval($totalAmount), $decimalPlaces);//round(floatval($ordersArray['totals']['totalSum']), $decimalPlaces);
											$v_ehf_data['legal_monetary_tax_inclusive'] = round(floatval($totalAmount), $decimalPlaces);//round(floatval($ordersArray['totals']['total']), $decimalPlaces);
											//$v_ehf_data['legal_monetary_allowance_total'] = ''; // optional
											//$v_ehf_data['legal_monetary_charge_total'] = ''; // optional
											//$v_ehf_data['legal_monetary_prepaid'] = ''; // optional
											//$v_ehf_data['legal_monetary_payable_rounding'] = ''; // optional
											$v_ehf_data['legal_monetary_payable_amount'] = round(floatval($totalAmount), $decimalPlaces);//round(floatval($ordersArray['totals']['total']), $decimalPlaces);
					
											$v_ehf_data['invoice_line'] = array();
											
											$v_item = array();
											$v_item['id'] = 1;//$order['orderId'];
											//$v_item['note'] = ''; // optional
											$v_item['quantity'] = (float)1;
											$v_item['amount'] = floatval($totalAmount);
											$v_item['accounting_cost'] = 'NO_REFERENCE'; // recommended
											$v_item['reference'] = (string)$invoiceNumber; // recommended
											//Optional >>
	//										$v_item['delivery_date'] = date("Y-m-d", strtotime($created_letter['created']));
	//										//$v_item['delivery_location_gln'] = '6754238987643';
	//										//$v_item['delivery_street'] = (!empty($order['delivery_address_line_1']) ? $order['delivery_address_line_1'] : '');
	//										//$v_item['delivery_street_additional'] = (!empty($order['delivery_address_line_2']) ? $order['delivery_address_line_2'] : '');
	//										$v_item['delivery_city'] = $v_settings['companypostalplace'];
	//										$v_item['delivery_postal_code'] = $v_settings['companyzipcode'];
	//										//$v_item['delivery_country_subentity'] = 'RegionD';
	//										$v_item['delivery_country'] = 'NO';//(($v_settings['companyCountry'] == '' || strtolower($v_settings['companyCountry']) == 'norge') ? 'NO' : $v_settings['companyCountry']);
											//Optional <<
											//Optional >>
	//											if(floatval($v_order['discountPercent'])>0)
	//											{
	//												$v_item['allowance_charge'] = array();
	//												$v_sub_item = array();
	//												$v_sub_item['type'] = 'false';
	//												$v_sub_item['reason'] = round(floatval($v_order['discountPercent']), $decimalPlaces).'% Rabatt';
	//												$v_sub_item['amount'] = round(floatval($v_order['pricePerPiece']) * floatval($v_order['amount']) * floatval($v_order['discountPercent']/100), $decimalPlaces);
	//												$v_item['allowance_charge'][] = $v_sub_item;
	//											}
											//$v_item['allowance_charge'] = array();
											//$v_sub_item = array();
											//$v_sub_item['type'] = 'false';
											//$v_sub_item['reason'] = 'Damage';
											//$v_sub_item['amount'] = '12';
											//$v_item['allowance_charge'][] = $v_sub_item;
											//$v_sub_item = array();
											//$v_sub_item['type'] = 'true';
											//$v_sub_item['reason'] = 'Testing';
											//$v_sub_item['amount'] = '12';
											//$v_item['allowance_charge'][] = $v_sub_item;
											//Optional <<
											//$v_item['description'] = array(array('text'=>proc_rem_style($order['articleName']))); // optional
											$v_item['name'] = 'See attached payment reminder';
											$v_item['sellers_item_identification'] = '1'; // optional
											//$v_item['standard_item_identification'] = '1234567890124'; // optional
											//$v_item['origin_country'] = 'DE'; // optional
											//$v_item['commodity_classification'] = array(array('id'=>'MP', 'code'=>'12344321'), array('id'=>'STI', 'code'=>'65434568')); // optional
											$v_item['classified_tax_category'] = 'E';
											$v_item['classified_tax_percent'] = (float)0; // optional
											$v_item['addition_item_property'] = array(); // optional
											//$v_sub_item = array();
											//$v_sub_item['name'] = 'Color';
											//$v_sub_item['value'] = 'Black';
											//$v_item['addition_item_property'][] = $v_item['id'];
											//Optional >>
											//$v_item['manufacturer_party_name'] = 'Company name ASA';
											//$v_item['manufacturer_party_org_nr'] = '904312347';
											//Optional <<
											$v_item['price'] = round(floatval($totalAmount), $decimalPlaces);
											//$v_item['base_quantity'] = 1; // optional
	//											if(floatval($v_order['discountPercent'])>0)
	//											{
	//												$v_item['price_allowance_charge'] = array(); // optional
	//												$v_sub_item = array();
	//												$v_sub_item['type'] = 'false';
	//												$v_sub_item['multiplier_factor'] = round(floatval($v_order['discountPercent']) / 100, $decimalPlaces*2);
	//												$v_sub_item['reason'] = round(floatval($v_order['discountPercent']), $decimalPlaces).'% Rabatt';
	//												$v_sub_item['amount'] = round(floatval($v_order['pricePerPiece']) * floatval($v_order['amount']) * floatval($v_order['discountPercent']/100), $decimalPlaces);
	//												$v_item['price_allowance_charge'][] = $v_sub_item;
	//											}
											//$v_item['price_allowance_charge'] = array(); // optional
											//$v_sub_item = array();
											//$v_sub_item['type'] = 'false';
											//$v_sub_item['reason'] = 'Contract';
											//$v_sub_item['multiplier_factor'] = '0.15';
											//$v_sub_item['amount'] = '225';
											//$v_sub_item['base_amount'] = '1500';
											//$v_item['price_allowance_charge'][] = $v_sub_item;
											$v_ehf_data['invoice_line'][] = $v_item;
					
											$files_attached = array(
												BASEPATH.$created_letter['pdf'],
											);
											
											$l_counter = 1;
											$v_ehf_data['additional_document_reference'] = array();
											
											foreach($files_attached as $s_file)
											{
												if(!is_file($s_file)) continue;
												if(filesize($s_file) > 5242880) continue; // Skip if larger than 5MB
												$s_mime_type = mime_content_type($s_file);
												if('application/pdf' == $s_mime_type)
												{
													$v_info = pathinfo($s_file);
													$v_item = array();
													$v_item['id'] = $v_info['basename'];
													$v_item['document_type'] = 'File'.$l_counter;
													$v_item['attachment_mime'] = $s_mime_type;
													$v_item['attachment_binary'] = base64_encode(file_get_contents($s_file));
													//$v_item['attachment_uri'] = 'http://www.suppliersite.eu/sheet001.html';
													$v_ehf_data['additional_document_reference'][] = $v_item;
													$l_counter++;
												}
											}
											$time_log[] = " start ehf create ".microtime();
											if(!function_exists('create_ehf_invoice')) include(BASEPATH.'modules/BatchInvoicing/procedure_create_invoices/scripts/CREATE_INVOICE/functions.php');
											$s_ehf_xml = create_ehf_invoice($v_ehf_data);
											// ************   EHF CREATION END ***********
											$time_log[]= " end ehf create ".microtime();
											
											$o_tmp_file = tmpfile();
											$s_tmp_file_path = stream_get_meta_data($o_tmp_file)['uri'];
											fwrite($o_tmp_file, $s_ehf_xml);
											$time_log[] = " ehf get info ".microtime();
					
											$ch = curl_init();
											curl_setopt($ch, CURLOPT_HEADER, 0);
											curl_setopt($ch, CURLOPT_VERBOSE, 0);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
											curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
											curl_setopt($ch, CURLOPT_POST, TRUE);
											curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
											curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300); //timeout in seconds
											curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
											curl_setopt($ch, CURLOPT_URL, 'https://ap2_api.getynet.com/index.php');
											$v_post = array(
												'file' => new CurlFile($s_tmp_file_path, 'text/csv', 'claim_letter_'.$created_letter['id'].'.xml'),
												'receiver' => '0192:'.$v_ehf_data['customer_org_nr'],
												'sender' => '0192:'.$v_ehf_data['supplier_org_nr'],
												'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
												'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
											);
				
											curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
											$s_response = curl_exec($ch);
				
											$time_log[] = " ehf sent ".microtime();
											$v_response = json_decode($s_response, TRUE);
											if(isset($v_response['status']) && $v_response['status'] == 1)
											{
												$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 5, sending_status = 1, sending_error_log='".$o_main->db->escape_str(json_encode($time_log))."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
												$o_query = $o_main->db->query($s_sql);
											} else {
												$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str('[EHF_AP_ERROR]:'.$s_response)."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
												$o_query = $o_main->db->query($s_sql);
											}
										} else if($created_letter['sending_action'] == 6){
											$l_send_status = 0;
											do{
												$code = generateRandomString(10);
												$code_check = null;
												$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
												$o_query = $o_main->db->query($s_sql, array($code));
												if($o_query){
													$code_check = $o_query->row_array();
												}
											} while($code_check != null);

											$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), print_batch_code = ?, sent_to_external_company = 2 WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($code, $created_letter['id']));
											$file_name_with_full_path = $created_letter['pdf'];
											if(file_exists(__DIR__."/../../../".$file_name_with_full_path)){
												$web_page_to_send = "https://min.bypost.no/dokumentsenter/mottak/4da0dd29-eb56-ee11-be6f-000d3ad8fd72"; //"https://pitofficeupload.bypost.no/caa2d405-2591-4f8c-8bc3-db7e4ae2dabe";

												$post_request = array(
													"file" => curl_file_create(__DIR__."/../../../".$file_name_with_full_path, "application/pdf",basename($file_name_with_full_path)) // for php 5.5+
												);
												$ch = curl_init();
												curl_setopt($ch, CURLOPT_URL, $web_page_to_send);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
												$result = curl_exec($ch);
												$http_status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
												curl_close($ch);
												if($http_status == 200){
													$l_send_status = 1;
													$created_letters[] = $created_letter;
												}

												if($l_send_status == 1){
													
													if($debitor && preg_replace('/\xc2\xa0/', '', trim($debitor['extra_invoice_email'])) != "" && $creditor) {
														if(file_exists(__DIR__."/../../../".$created_letter['pdf'])){
															$s_email_subject = $formText_ReminderFrom_output." ".$creditor['companyname'];
				
															$s_email_body = $formText_Hi_pdf." ".$debitor['fullName']."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['companyname']."<br/>";
															if($companyPhone != "") {
																$s_email_body .= $formText_Phone_pdf." ".$companyPhone;
															}
															$s_email_body.="<br/><br/>".$formText_ThisEmailSentFromReminderSystemOflow_output." (<a href='".$formText_realWebAddressAtBottomOfEmail_output."'>".$formText_realWebAddressAtBottomOfEmail_output."</a>)";
				
				
															$s_sql = "select * from sys_emailserverconfig order by default_server desc";
															$o_query = $o_main->db->query($s_sql);
															$v_email_server_config = $o_query ? $o_query->row_array() : array();
				
															$s_sql = "INSERT INTO sys_emailsend 
															(id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) 
															VALUES (NULL, NOW(), ?, 2, NOW(), ?, ?, 0, 0, ?, 'collecting_cases_claim_letter', '', 0, ?, ?, ?);";
															$o_main->db->query($s_sql, array($companyEmail, $creditor['companyname'], $collecting_system_settings['reminder_sender_email'], $created_letter['id'], $s_email_subject, $s_email_body, $batch_id));
															$l_emailsend_id = $o_main->db->insert_id();
															$invoiceEmail_string = str_replace(",",";",preg_replace('/\xc2\xa0/', '', trim($debitor['extra_invoice_email'])));
															$invoiceEmails = explode(";", $invoiceEmail_string);
				
																// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
																//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
																// Trim rest spaces and new lines
																//$invoiceEmail = trim($invoiceEmail);
															if(count($invoiceEmails) > 0){
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
																		if($v_email_server_config['host'] == "mail3.getynetmail.com"){
																			$mail->SMTPSecure = 'ssl';
																		}
				
																	}
																} else {
																	$mail->Host = "mail.dcode.no";
																}
																if($companyEmail != "") {
																	$mail->addReplyTo($companyEmail, $creditor['companyname']);
																}
																$mail->From		= $collecting_system_settings['reminder_sender_email'];
																$mail->FromName	= $creditor['companyname'];
																$mail->Subject	= $s_email_subject;
																$mail->Body		= $s_email_body;
																
																$emailAdded = false;
																foreach($invoiceEmails as $invoiceEmail){
																	if(filter_var(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)), FILTER_VALIDATE_EMAIL))
																	{
																		$emailAdded = true;
																		$mail->AddAddress(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)));
																	}
																}
																$mail->AddBCC("david@dcode.no");
																// $mail->AddBCC("byamba@dcode.no");
																$mail->AddAttachment(__DIR__."/../../../".$created_letter['pdf']);
																if($emailAdded){
																	$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
																	$o_main->db->query($s_sql, array($l_emailsend_id, $creditor['companyname'], preg_replace('/\xc2\xa0/', '', trim($invoiceEmail))));
																	$l_emailsendto_id = $o_main->db->insert_id();
				
																	if($mail->Send())
																	{
																		$emails_sent++;
																		$s_sql = "UPDATE collecting_cases_claim_letter SET performed_date = NOW(), performed_action = 1, sending_status = 1 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
																		$o_query = $o_main->db->query($s_sql);
																		$created_letters[] = $created_letter;
																	} else {
																		$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message ='".$o_main->db->escape_str($mail->ErrorInfo)."' WHERE id = '".$o_main->db->escape_str($l_emailsendto_id)."'";
																		$o_query = $o_main->db->query($s_sql);
																	}
																} else {
																	$sending_error = "invalid email";
																}
															}
														}
													}
												} else {
													$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str(json_encode($http_status))."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
													$o_query = $o_main->db->query($s_sql);
												}
											}										
										}
									} else {
										$sending_error = "missing case";
									}
								}
							} else {
								$sending_error = "negative amount";
							}
						} else {
							$sending_error = "missing pdf for letter";
						}
					} else {
						$sending_error = "Missing sending action";
					}
				} else {
					$sending_error = "Demo account";
				}


				if($sending_error != "") {
					$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str($sending_error)."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
					$o_query = $o_main->db->query($s_sql);
				}
			} catch(Exception $e) {				
				$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 0, sending_error_log = '".$o_main->db->escape_str($e->getMessage())."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			}
		}
	}

	// $l_next_run = strtotime($v_auto_task['next_run']) + 60;
	// $v_auto_task['next_run'] = date("Y-m-d H:i:s", $l_next_run);
	$x = 0;
	do {
		$l_next_run = strtotime($v_auto_task['next_run']) + 60;
		$v_auto_task['next_run'] = date("Y-m-d H:i:s", $l_next_run);
		$x++;
	} while($l_next_run<time() && $x<100);

	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
	$v_return['sql'] = $o_main->db->queries;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
