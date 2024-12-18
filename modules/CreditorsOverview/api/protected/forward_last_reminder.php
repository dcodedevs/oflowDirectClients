<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

$_POST = $v_data['params']['post'];

$case_id = $_POST['case_id'];
$body = $_POST['body'];
$username= $v_data['params']['username'];
$languageID = 'no';

$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($case_id));
$collecting_cases_claim_letter = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM collecting_system_settings";
$o_query = $o_main->db->query($s_sql, array($case_id));
$collecting_system_settings = $o_query ? $o_query->row_array() : array();

if($collecting_cases_claim_letter){

    $s_sql = "SELECT collecting_cases.* FROM collecting_cases WHERE collecting_cases.id = ?";
    $o_query = $o_main->db->query($s_sql, array($collecting_cases_claim_letter['case_id']));
    $case = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT *, concat_ws(' ', customer.name, customer.middlename, customer.lastname) as fullName FROM customer WHERE customer.id = ?";
	$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
	$debitor = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
    $creditor = ($o_query ? $o_query->row_array() : array());

    ob_start();
    include(__DIR__."/../../output/languagesOutput/default.php");
    if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
        include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
    }

	include_once(__DIR__."/../../../CollectingCaseClaimletter/output/languagesOutput/no.php");

	$companyPhone = $creditor['companyphone'];
	$companyEmail = $creditor['companyEmail'];
	if($creditor['use_local_email_phone_for_reminder']) {
		$companyPhone = $creditor['local_phone'];
		$companyEmail = $creditor['local_email'];
	}

	$s_email_subject = $formText_ReminderFrom_output." ".$creditor['companyname'];

	$s_email_body = $formText_Hi_pdf." ".$debitor['fullName']."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['companyname']."<br/>";
	if($companyPhone != "") {
		$s_email_body .= $formText_Phone_pdf." ".$companyPhone;
	}
	$s_email_body.="<br/><br/>".$formText_ThisEmailSentFromReminderSystemOflow_output." (<a href='".$formText_realWebAddressAtBottomOfEmail_output."'>".$formText_realWebAddressAtBottomOfEmail_output."</a>)";



	if($_POST['output_form_submit']) {
		if($_POST['email'] != "" && $_POST['body']) {
			$s_email_body = $_POST['body'];
			$s_sql = "select * from sys_emailserverconfig order by default_server desc";
			$o_query = $o_main->db->query($s_sql);
			$v_email_server_config = $o_query ? $o_query->row_array() : array();

			$invoiceEmail_string = str_replace(",",";",preg_replace('/\xc2\xa0/', '', trim($_POST['email'])));
			$invoiceEmails = explode(";", $invoiceEmail_string);
				// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
				//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
				// Trim rest spaces and new lines
				//$invoiceEmail = trim($invoiceEmail);
			if(count($invoiceEmails) > 0) {
				$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'collecting_cases_claim_letter', '', 0, ?, ?, ?);";
				$o_main->db->query($s_sql, array($creditor['sender_name'], $creditor['sender_email'], $collecting_cases_claim_letter['id'], $s_email_subject, $s_email_body.json_encode($invoiceEmails), $batch_id));
				$l_emailsend_id = $o_main->db->insert_id();

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
				// $mail->AddBCC("david@dcode.no");
				$mail->AddAttachment(__DIR__."/../../../../".$collecting_cases_claim_letter['pdf']);
				if($emailAdded){
					$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
					$o_main->db->query($s_sql, array($l_emailsend_id, $creditor['companyname'], preg_replace('/\xc2\xa0/', '', trim($invoiceEmail))));
					$l_emailsendto_id = $o_main->db->insert_id();

					if($mail->Send())
					{
						$emails_sent++;
					} else {
						$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message ='".$o_main->db->escape_str($mail->ErrorInfo)."' WHERE id = '".$o_main->db->escape_str($l_emailsendto_id)."'";
						$o_query = $o_main->db->query($s_sql);
					}
				} else {
					$sending_error = "invalid email";
				}
			}
		}
	} else {
		$v_return['status'] = 1;
		$v_return['collecting_cases_claim_letter'] = $collecting_cases_claim_letter;
		$v_return['s_email_subject'] = $s_email_subject;
		$v_return['s_email_body'] = $s_email_body;
	}
} else {
    $v_return['error'] = 'Missing letter';
}
?>
