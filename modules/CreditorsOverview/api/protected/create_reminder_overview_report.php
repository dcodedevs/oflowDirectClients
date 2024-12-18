<?php 
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

$debitor_id= $v_data['params']['debitor_id'];
$creditor_filter= $v_data['params']['creditor_filter'];
$username = $v_data['params']['username'];
$overview_send_by = $v_data['params']['overview_send_by'];
$custom_email =  trim($v_data['params']['custom_email']);
$additional_emails =  $v_data['params']['additional_emails'];

require(__DIR__."/functions/fnc_generate_reminder_overview_report.php");

include(dirname(__FILE__).'/../languagesOutput/no.php');
if($debitor_id > 0 && $creditor_filter > 0) {   
    $s_sql = "SELECT * FROM collecting_system_settings";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_filter));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT *, concat_ws(' ',customer.name, customer.middlename, customer.lastname) as fullName FROM customer WHERE creditor_id = ? AND id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_filter, $debitor_id));
    $debitor = ($o_query ? $o_query->row_array() : array());

    if(($overview_send_by == 1 && $custom_email != "") || ($overview_send_by == 0 && trim($debitor['invoiceEmail']) != "")) {
        $s_sql = "UPDATE customer SET updated=NOW(), updatedBy = ?, overview_send_by = ?, overview_send_custom_email = ? WHERE id = ? and creditor_id = ?";
        $o_query = $o_main->db->query($s_sql, array($username, $overview_send_by, $custom_email, $debitor_id, $creditor_filter));

        $v_return['item'] = generate_report($creditor_filter, $debitor_id);
        
        if($v_return['item']['report_id'] > 0){
            $v_return['status'] = 1;
            $report_id = $v_return['item']['report_id'];
            $invoiceEmail = trim($debitor['invoiceEmail']);
            if($overview_send_by == 1) {
                $invoiceEmail = $custom_email;
            }
            $total_emails[] = $invoiceEmail;
            foreach($additional_emails as $additional_email){
                if(trim($additional_email) != ""){
                    $total_emails[] = trim($additional_email);
                }
            }
            foreach($total_emails as $total_email){
                $s_sql = "INSERT INTO creditor_debitor_reminder_overview_report_sendings SET created = now(), moduleID = ?, creditor_debitor_reminder_overview_report_id = ?, sending_email = ?".($o_main->multi_acc?", account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
                $o_query = $o_main->db->query($s_sql, array($module_data['uniqueID'], $report_id, $total_email));
                if($o_query) {
                    $sending_id =  $o_main->db->insert_id();
                    // Sending config
                    $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig".($o_main->multi_acc?" WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." order by default_server desc");
                    $v_email_server_config = $v_email_server_config_sql ? $v_email_server_config_sql->row_array() : array();
                    $companyPhone = $creditor['companyphone'];
                    $companyEmail = $creditor['companyEmail'];
                    if($creditor['use_local_email_phone_for_reminder']) {
                        $companyPhone = $creditor['local_phone'];
                        $companyEmail = $creditor['local_email'];
                    }
                    $mail = new PHPMailer();
                    try {
                        $mail->CharSet	= 'UTF-8';
                        $mail->IsSMTP(true);
                        $mail->isHTML(true);
                        if($v_email_server_config['host'] != "")
                        {
                            $mail->Host = $v_email_server_config['host'];
                            if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

                            if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
                            {
                                $mail->SMTPAuth = true;
                                $mail->SMTPSecure = 'ssl';
                                $mail->Username = $v_email_server_config['username'];
                                $mail->Password = $v_email_server_config['password'];
                            }
                        } else {
                            $mail->Host = "mail.dcode.no";
                        }
                        if($companyEmail != "") {
                            $mail->addReplyTo($companyEmail, $creditor['companyname']);
                        }
                        $subject = $formText_UnpaidInvoicesOverviewFrom." ".$creditor['companyname'];
                        $s_email_body = $formText_Hi_pdf." ".$debitor['fullName']."<br/><br/>".$formText_AttachedCurrentReminderOverviewReport_output."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['companyname']."<br/>";
                        if($companyPhone != "") {
                            $s_email_body .= $formText_Phone_pdf." ".$companyPhone;
                        }
                        $s_email_body.="<br/><br/>".$formText_ThisEmailSentFromReminderSystemOflow_output." (<a href='".$formText_realWebAddressAtBottomOfEmail_output."'>".$formText_realWebAddressAtBottomOfEmail_output."</a>)";

                        // Send
                        $mail->From		= $collecting_system_settings['reminder_sender_email'];
                        $mail->FromName	= $creditor['companyname'];
                        $mail->Subject  = $subject;
                        $mail->Body		= $s_email_body;
                        $mail->AddAddress($total_email);
                        
                        $s_pdf_file_path = __DIR__."/../../../../".$v_return['item']['pdf'];
                        $mail->AddAttachment($s_pdf_file_path);

                        if (!$mail->Send()) {
                            $error = $mail->ErrorInfo;
                            $sent_status = false;
                            $s_sql = "UPDATE creditor_debitor_reminder_overview_report_sendings SET sending_status = ?, sending_log = ? WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
                            $o_query = $o_main->db->query($s_sql, array(0, $error, $sending_id));
                        } else {
                            $sent_status = true;
                            $s_sql = "UPDATE creditor_debitor_reminder_overview_report_sendings SET sending_status = ? WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
                            $o_query = $o_main->db->query($s_sql, array(1, $sending_id));
                        }
                    } catch (phpmailerException $e) {
                        $error = $e->errorMessage(); //Pretty error messages from PHPMailer
                        $s_sql = "UPDATE creditor_debitor_reminder_overview_report_sendings SET sending_status = ?, sending_log = ? WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
                        $o_query = $o_main->db->query($s_sql, array(0, $error, $sending_id));
                    } catch (Exception $e) {
                        $error = $e->getMessage(); //Boring error messages from anything else!                    
                        $s_sql = "UPDATE creditor_debitor_reminder_overview_report_sendings SET sending_status = ?, sending_log = ? WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
                        $o_query = $o_main->db->query($s_sql, array(0, $error, $sending_id));
                    }
                }
            }
        }
    } else {        
        $v_return['error'] = $formText_MissingEmail_output;
    }
}

?>