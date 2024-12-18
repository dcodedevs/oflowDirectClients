<?php
$ip = $v_data['params']['ip'];
$code = $v_data['params']['code'];
$type = $v_data['params']['type'];
$pause_reason = $v_data['params']['pause_reason'];
$collectingcase_id = $v_data['params']['collectingcase_id'];
$case_type = $v_data['params']['case_type'];
$message = $v_data['params']['message'];
include(__DIR__."/../languagesOutput/default.php");
if($v_data['params']['languageID'] != "" && $v_data['params']['languageID'] != "en"){
    include(__DIR__."/../languagesOutput/".$v_data['params']['languageID'].".php");
} else {
    include(__DIR__."/../languagesOutput/no.php");
}
include(__DIR__."/../../../../fw/account_fw/includes/class.phpmailer.php");
include_once(__DIR__."/../../../CreditorsOverview/output/includes/fnc_process_open_cases_for_tabs.php");

if($message != "" && $collectingcase_id != "" && ($type != "" || $pause_reason != "")) {
    $s_sql = "select * from collecting_cases_debitor_codes_log where ip = ? AND successful = 0 AND created BETWEEN  DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')  AND  DATE_FORMAT(NOW(), '%Y-%m-%d %H:59:59')";
    $o_query = $o_main->db->query($s_sql, array($ip));
    $attempts = $o_query ? $o_query->result_array() : array();

    if(count($attempts) < 3){
        $s_sql = "INSERT INTO collecting_cases_debitor_codes_log SET code_used = ?, successful = 0, ip = ?, created = NOW()";
        $o_query = $o_main->db->query($s_sql, array($code, $ip));
        if($o_query){
            $log_id = $o_main->db->insert_id();
            $s_sql = "select * from collecting_cases_debitor_codes where code = ? AND expiration_time > NOW()";
            $o_query = $o_main->db->query($s_sql, array($code));
            $key_item = $o_query ? $o_query->row_array() : array();
            if($key_item) {
                $s_sql = "UPDATE collecting_cases_debitor_codes_log SET successful = 1 WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($log_id));
				if($case_type == "collecting"){
	                $s_sql = "INSERT INTO collecting_company_case_paused SET
	                id=NULL,
	                created = now(),
	                createdBy= ?,
	                collecting_company_case_id = ?,
					created_date = NOW(),
					pause_reason = ?,
					pause_reason_comment = ?,
                    incoming_from_portal = 1";
	                $o_query = $o_main->db->query($s_sql, array($ip, $collectingcase_id, $pause_reason, $message));
				} else {
					$s_sql = "INSERT INTO collecting_cases_objection SET
	                id=NULL,
	                created = now(),
	                createdBy= ?,
	                collecting_case_id = ?,
	                objection_type_id = ?,
	                message_from_debitor = ?";
	                $o_query = $o_main->db->query($s_sql, array($ip, $collectingcase_id, $type, $message));
				}
                if($o_query){
                    $objectionId = $o_main->db->insert_id();
                    $v_return['status'] = 1;

					if($case_type == "collecting") {
	                    $s_sql = "select * from collecting_company_cases where id = ?";
	                    $o_query = $o_main->db->query($s_sql, array($collectingcase_id));
	                    $collecting_case = $o_query ? $o_query->row_array() : array();
						$objection_table = "collecting_company_case_paused";
					} else {
	                    $s_sql = "select * from collecting_cases where id = ?";
	                    $o_query = $o_main->db->query($s_sql, array($collectingcase_id));
	                    $collecting_case = $o_query ? $o_query->row_array() : array();
                        if($collecting_case){
                            $s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1  WHERE collectingcase_id = ? AND creditor_id = ?";
                            $o_query = $o_main->db->query($s_sql, array($collecting_case['id'], $collecting_case['creditor_id']));
                            
                            //trigger reordering 							
                            process_open_cases_for_tabs($collecting_case['creditor_id'], 3);
                        }
                        
						$objection_table = "collecting_cases_objection";
					}
                    $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($collecting_case['debitor_id']));
                    $customer = ($o_query ? $o_query->row_array() : array());

                    $s_sql = "SELECT creditor.*  FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
                    $creditor = ($o_query ? $o_query->row_array() : array());

    				$s_sql = "SELECT * FROM accountinfo_emailsender_accountconfig";
                    $o_query = $o_main->db->query($s_sql);
                    $accountinfo_emailsender_accountconfig = ($o_query ? $o_query->row_array() : array());
                    if($accountinfo_emailsender_accountconfig['name'] != "" && $accountinfo_emailsender_accountconfig['email']){
                        $emailsToBeNotified = $creditor['emails_for_notification'];
                        $emailsToBeNotified = str_replace(";", ",", $emailsToBeNotified);
                        $invoiceEmails = explode(",", $emailsToBeNotified);

                        $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig order by default_server desc");
                        $v_email_server_config = $v_email_server_config_sql ? $v_email_server_config_sql->row_array() : array();

                        foreach($invoiceEmails as $invoiceEmail){
                            $invoiceEmail = trim($invoiceEmail);
                            if($invoiceEmail != "") {
                                $mail = new PHPMailer;
                                $mail->CharSet  = 'UTF-8';
                                $mail->IsSMTP(true);
                                $mail->isHTML(true);
                                if($v_email_server_config['host'] != "")
                                {
                                    $mail->Host = $v_email_server_config['host'];
                                    if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

                                    if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
                                    {
                                        $mail->SMTPAuth = true;
                                        $mail->Username = $v_email_server_config['username'];
                                        $mail->Password = $v_email_server_config['password'];
                                    }
                                } else {
                                    $mail->Host = "mail.dcode.no";
                                }
                                $s_email_subject = $formText_Objection_output." ".$collecting_case['id'];
                                $s_email_body = $formText_ObjectionWasAddedForCase."<br/>";
                                $s_email_body .= $formText_Case_output.": ".$collecting_case['id']."<br/>";
                                $s_email_body .= $formText_Customer_output.": ".$customer['name']." ".$customer['middlename']." ".$customer['lastname']."<br/>";
                                $s_email_body .= $formText_MessageFromDebitor.": ".$message."<br/>";

                                $mail->From     = $accountinfo_emailsender_accountconfig['email'];
                                $mail->FromName = $accountinfo_emailsender_accountconfig['name'];
                                $mail->Subject  = $s_email_subject;
                                $mail->Body     = $s_email_body;
                                $mail->AltBody  = strip_tags($s_email_body);
                                $mail->AddAddress($invoiceEmail, $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']);

                                // $atached_files = json_decode($offer['files_attached_to_email'], true);
                                // foreach($atached_files as $attached_file){
                                //     $attachmentFile = __DIR__."/../../../../".$attached_file[1][0];
                                //     $mail->AddAttachment($attachmentFile);
                                // }

                                $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), 'webpage', 2, NOW(), '', 'webpage', 0, 0, '".$objectionId."', '".$objection_table."', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
                                $o_main->db->query($s_sql);
                                $l_emailsend_id = $o_main->db->insert_id();

                                $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '', '".addslashes($invoiceEmail)."', 1, '', NOW(), 1);";
                                $o_main->db->query($s_sql);
                                $l_emailsendto_id = $o_main->db->insert_id();

                                if($mail->Send())
                                {

                                } else {
                                    $v_return['error'] = $formText_ErrorSendingEmail_output;

                                    $s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = '".json_encode($mail)."' WHERE id = ?";
                                    $o_main->db->query($s_sql, array($l_emailsendto_id));

                                    $mail = new PHPMailer;
                                    $mail->CharSet  = 'UTF-8';
                                    $mail->IsSMTP(true);
                                    $mail->isHTML(true);
                                    if($v_email_server_config['host'] != "")
                                    {
                                        $mail->Host = $v_email_server_config['host'];
                                        if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

                                        if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
                                        {
                                            $mail->SMTPAuth = true;
                                            $mail->Username = $v_email_server_config['username'];
                                            $mail->Password = $v_email_server_config['password'];
                                        }
                                    } else {
                                        $mail->Host = "mail.dcode.no";
                                    }
                                    $mail->From     = "noreply@getynet.com";
                                    $mail->FromName = "Getynet.com";
                                    $mail->Subject  = $formText_NotDelivered_Output.": ".$s_email_subject;
                                    $mail->Body     = $s_email_body;
                                    $mail->AddAddress(trim($v_email_server_config['technical_email']));
                                    // $mail->AddAttachment($invoiceFile);
                                    // foreach($files_attached as $file_to_attach) {
                                    //     $mail->AddAttachment(__DIR__."/../../../../".$file_to_attach[1][0]);
                                    // }

                                }
                            }
                        }
                    }
                } else {
                    $v_return['error'] = 'Wrong/expired code';
                }

            }
        }
    } else {
        $v_return['error'] = "Too many wrong requests. You have been suspended for 1 hour.";
    }
} else {
    $v_return['error'] = "Missing fields";
}
?>
