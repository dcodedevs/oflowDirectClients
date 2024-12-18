<?php
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
$s_sql = "SELECT c.*, a.id AS action_id FROM collecting_cases_handling_action a JOIN collecting_cases c ON c.id = a.collecting_case_id
LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
AND (a.action_type = 2 OR (a.action_type = 4 AND (cust.invoiceEmail <> '' AND cust.invoiceEmail is not null))) AND a.collecting_cases_process_steps_action_id is not null
ORDER BY c.id";
$o_query = $o_main->db->query($s_sql);
$cases = $o_query ? $o_query->result_array() : array();
$casesToGenerate = $_POST['casesToGenerate'];
$created_letters = 0;
if(count($casesToGenerate) > 0 && count($cases) > 0){
	$s_sql = "select * from sys_emailserverconfig order by default_server desc";
	$o_query = $o_main->db->query($s_sql);
	$v_email_server_config = $o_query ? $o_query->row_array() : array();

	if($v_email_server_config) {
		do{
			$code = generateRandomString(10);
			$batch_check = null;
			$s_sql = "SELECT * FROM collecting_cases_batch WHERE code = ?";
			$o_query = $o_main->db->query($s_sql, array($code));
			if($o_query){
				$batch_check = $o_query->row_array();
			}
		} while($batch_check != null);
		$s_sql = "INSERT INTO collecting_cases_batch SET id=NULL, createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), code=?";
		$o_query = $o_main->db->query($s_sql, array($code));
		$batch_id = $o_main->db->insert_id();
		foreach($cases as $case)
		{
			if(in_array($case['id'], $casesToGenerate)){
			    $s_sql = "SELECT creditor.*, customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
			    $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
			    $creditor = ($o_query ? $o_query->row_array() : array());

				$s_sql = "SELECT * FROM customer WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
				$customer = $o_query ? $o_query->row_array() : array();
				if($customer && $customer['invoiceEmail'] != "" && $creditor){

				    $s_sql = "SELECT * FROM collecting_cases_handling_action WHERE id = ?";
				    $o_query = $o_main->db->query($s_sql, array($case['action_id']));
				    $handling_action = ($o_query ? $o_query->row_array() : array());
					if($handling_action){
						$s_sql = "SELECT * FROM collecting_cases_process_steps_action WHERE id = ?";
				        $o_query = $o_main->db->query($s_sql, array($handling_action['collecting_cases_process_steps_action_id']));
				        $collecting_cases_process_steps_action = ($o_query ? $o_query->row_array() : array());
				        if($collecting_cases_process_steps_action){
							$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.id = ?";
				            $o_query = $o_main->db->query($s_sql, array($collecting_cases_process_steps_action['collecting_cases_process_steps_id']));
				            $process_step = ($o_query ? $o_query->row_array() : array());

							$s_email_subject = $process_step['name']." ".$formText_From_pdf." ".$creditor['name']." ".$creditor['middlename']." ".$creditor['lastname'];
							$s_email_body = $formText_Hi_pdf."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['name']." ".$creditor['middlename']." ".$creditor['lastname']."<br/>";
							if($creditor['phone'] != "") {
								$s_email_body .= $formText_Phone_pdf." ".$creditor['phone'];
							}
							$o_curl = curl_init();
							$s_url = $extradomaindirroot.'/modules/CollectingCases/output/includes/generatePdf.php?caseId='.$case['id'].'&batch_id='.$batch_id.'&action_id='.$case['action_id'];

				            curl_setopt($o_curl, CURLOPT_URL, $s_url);
							curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
							$s_response = curl_exec($o_curl);
							curl_close($o_curl);
						    if($s_response !== FALSE)
							{
								$v_response = json_decode($s_response, TRUE);
								if(isset($v_response['status']) && 1 == $v_response['status'])
								{
									$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? AND action_id = ?";
			                        $o_query = $o_main->db->query($s_sql, array($case['id'], $case['action_id']));
			                        $letter = $o_query ? $o_query->row_array() : array();

									$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'collecting_cases', '', 0, ?, ?, ?);";
									$o_main->db->query($s_sql, array($variables->loggID, $v_settings['invoiceFromEmail'], $case['id'], $s_email_subject, $s_email_body, $batch_id));
									$l_emailsend_id = $o_main->db->insert_id();
									$emailsArray = array($customer['invoiceEmail']);
									foreach($emailsArray as $invoiceEmail)
									{
										// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
										//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
										// Trim rest spaces and new lines
										//$invoiceEmail = trim($invoiceEmail);
										if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL))
										{
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
											$mail->From		= $creditor['sender_email'];
											$mail->FromName	= $creditor['sender_name'];
											$mail->Subject	= $s_email_subject;
											$mail->Body		= $s_email_body;
											$mail->AddAddress($invoiceEmail);
											// $mail->AddAttachment($filepath.$file);
											$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
											$o_main->db->query($s_sql, array($l_emailsend_id, $customer['name']." ".$customer['middlename']." ".$customer['lastname'], $invoiceEmail));
											$l_emailsendto_id =$o_main->db->insert_id();

											if($mail->Send())
											{
												$created_letters++;
												$s_sql = "UPDATE collecting_cases_handling_action SET performed_date = NOW(), performed_action = 1 WHERE id = '".$o_main->db->escape_str($case['action_id'])."'";
									    		$o_query = $o_main->db->query($s_sql);

											} else {
												$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = ? WHERE id = ?";
												$o_main->db->query($s_sql, array(json_encode($mail), $l_emailsendto_id));

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
												$mail->From		= "noreply@getynet.com";
												$mail->FromName	= "Getynet.com";
												$mail->Subject	= $formText_NotDelivered_Output.": ".$s_email_subject;
												$mail->Body		= $s_email_body;
												$mail->AddAddress($v_email_server_config['technical_email']);
												// $mail->AddAttachment($filepath.$file);
												// foreach($files_attached as $file_to_attach) {
												// 	$mail->AddAttachment(__DIR__."/../../../../../".$file_to_attach[1][0]);
												// }
											}
										} else {
										}

									}
								}
							}
						}
					}
				}
			}
		}
	}
}


if($created_letters > 0){
	$fw_return_data = array(
		'status' => 1,
		'batch_id' => $batch_id,
	);
} else {
	$fw_error_msg[] = $formText_NoEmailSent_output;
}
