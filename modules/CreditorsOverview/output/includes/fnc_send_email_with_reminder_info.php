<?php 
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');
if(!function_exists("send_email_with_reminder_info")){
    function send_email_with_reminder_info($creditor, $invoiceEmails, $force_send = false){
        global $o_main;    
        if(isset($o_main->dcache['collecting_system_settings'])) {
            $collecting_system_settings = $o_main->dcache['collecting_system_settings']; 
        } else {
            $s_sql = "SELECT * FROM collecting_system_settings";
            $o_query = $o_main->db->query($s_sql);
            $collecting_system_settings = $o_query ? $o_query->row_array() : array();
            $o_main->dcache['collecting_system_settings'] = $collecting_system_settings;
        }
        
        define('BASEPATH', realpath(__DIR__.'/../../../').'/');
        include_once(__DIR__."/../output/includes/readOutputLanguage.php");
        include(__DIR__."/../languagesOutput/no.php");
        require_once(__DIR__."/creditor_functions_v2.php");
        if($creditor['activate_email_with_todays_reminders'] || $force_send) {
            $sendToday = false;	
            $date_number = date('N');
            if($date_number >= 1 && $date_number <= 5){
                $sendToday = true;
            }
            // if($creditor['email_sending_day_choice_reminder'] == 0) {
            //     $sendToday = true;
            // } else {
            //     $s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 0";
            //     $o_query = $o_main->db->query($s_sql, array($creditor['id']));
            //     $creditor_email_sending_days = ($o_query ? $o_query->result_array() : array());
            //     foreach($creditor_email_sending_days as $creditor_email_sending_day) {
            //         if($creditor_email_sending_day['day_number'] == date('N')) {
            //             if($creditor_email_sending_day['checked']){
            //                 $sendToday = true;
            //             }
            //         }
            //     }
            // }
            if($sendToday){	
                $filters = array();
                $filters['list_filter'] = "canSendReminderNow";
                $filters['sublist_filter'] = "manual_move";
                $filters['order_field'] = 'debitor';
                $manual_can_send_reminders = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);

                $filters = array();
                $filters['list_filter'] = "canSendReminderNow";
                $filters['sublist_filter'] = "automatic_move";
                $filters['order_field'] = 'debitor';
                $automatic_can_send_reminders = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                
                
                $filters = array();
                $filters['list_filter'] = "canSendReminderNow";
                $filters['sublist_filter'] = "missing_address";
                $filters['order_field'] = 'debitor';
                $missing_address_can_send_reminders = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                
                $filters = array();
                $filters['list_filter'] = "canSendReminderNow";
                $filters['sublist_filter'] = "small_amount";
                $filters['order_field'] = 'debitor';
                $small_amount_can_send_reminders = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                

            }
            // if($creditor['email_sending_day_choice_move'] == 0){
            //     $sendToday = true;
            // } else {
            //     $s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 1";
            //     $o_query = $o_main->db->query($s_sql, array($creditor['id']));
            //     $creditor_email_sending_days = ($o_query ? $o_query->result_array() : array());
            //     foreach($creditor_email_sending_days as $creditor_email_sending_day) {
            //         if($creditor_email_sending_day['day_number'] == date('N')) {
            //             if($creditor_email_sending_day['checked']){
            //                 $sendToday = true;
            //             }
            //         }
            //     }
            // }
            if($sendToday) {	
                $filters = array();
                $filters['list_filter'] = "notPayedConsiderCollectingProcess";
                $filters['sublist_filter'] = "manual_move";
                $filters['order_field'] = 'debitor';
                $manual_move_to_collecting = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                
                $filters = array();
                $filters['list_filter'] = "notPayedConsiderCollectingProcess";
                $filters['sublist_filter'] = "automatic_move";
                $filters['order_field'] = 'debitor';
                $automatic_move_to_collecting = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                
                $filters = array();
                $filters['list_filter'] = "notPayedConsiderCollectingProcess";
                $filters['sublist_filter'] = "missing_address";
                $filters['order_field'] = 'debitor';
                $missing_address_move_to_collecting = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                
                $filters = array();
                $filters['list_filter'] = "notPayedConsiderCollectingProcess";
                $filters['sublist_filter'] = "small_amount";
                $filters['order_field'] = 'debitor';
                $small_amount_move_to_collecting = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
            }

            $s_email_subject = $formText_CasesThatWillBeProcessedToday_output." ".$creditor['companyname'];
            $s_email_body = $formText_HiEmail_output;
            $s_email_body .= "<br/>".$formText_CasesThatWillBeProcessedTodayExplanation_output;
            if(count($automatic_can_send_reminders) > 0 || count($manual_can_send_reminders) > 0 || count($missing_address_can_send_reminders) > 0  || count($small_amount_can_send_reminders) > 0) {
                if(count($automatic_can_send_reminders) > 0) {
                    $s_email_body .='<table width="800">
                            <tr>
                                <td width="800" class="regular">
                                    <h1 class="title" style="font-size: 20px">'.$formText_RemindersEmail_output.' - '.$formText_AutomaticEmail_output.'</h1>
                                </td>
                            </tr>
                            <tr>
                                <td width="800" class="regular">
                                    <table width="800" border="1" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td>
                                                '.$formText_CustomerName_output.'
                                            </td>
                                            <td>
                                                '.$formText_InvoiceNumber_output.'
                                            </td>
                                            <td>
                                                '.$formText_Date_output.'
                                            </td>
                                            <td>
                                                '.$formText_DueDate_output.'
                                            </td>
                                            <td>
                                                '.$formText_OriginalAmount_output.'
                                            </td>
                                            <td>
                                                '.$formText_Balance_output.'
                                            </td>
                                        </tr>';
                                    foreach($automatic_can_send_reminders as $manual_can_send_reminder) {
                                        $s_email_body .= '<tr>
                                            <td>
                                                '.$manual_can_send_reminder['customer_name'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['invoice_nr'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['date'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['due_date'].'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                            </td>
                                        </tr>';
                                    }
                    $s_email_body .=	'</table>
                                    </td>
                                </tr>
                            </table>';
                }
                if(count($manual_can_send_reminders) > 0) {
                    $s_email_body .='<table width="800">
                            <tr>
                                <td width="800" class="regular">
                                    <h1 class="title" style="font-size: 20px">'.$formText_RemindersEmail_output.' - '.$formText_ManualEmail_output.'</h1>
                                </td>
                            </tr>
                            <tr>
                                <td width="800" class="regular">
                                    <table width="800" border="1" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td>
                                                '.$formText_CustomerName_output.'
                                            </td>
                                            <td>
                                                '.$formText_InvoiceNumber_output.'
                                            </td>
                                            <td>
                                                '.$formText_Date_output.'
                                            </td>
                                            <td>
                                                '.$formText_DueDate_output.'
                                            </td>
                                            <td>
                                                '.$formText_OriginalAmount_output.'
                                            </td>
                                            <td>
                                                '.$formText_Balance_output.'
                                            </td>
                                        </tr>';
                                    foreach($manual_can_send_reminders as $manual_can_send_reminder) {
                                        $s_email_body .= '<tr>
                                            <td>
                                                '.$manual_can_send_reminder['customer_name'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['invoice_nr'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['date'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['due_date'].'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                            </td>
                                        </tr>';
                                    }
                    $s_email_body .=	'</table>
                                    </td>
                                </tr>
                            </table>';
                }
                if(count($missing_address_can_send_reminders) > 0) {
                    $s_email_body .='<table width="800">
                            <tr>
                                <td width="800" class="regular">
                                    <h1 class="title" style="font-size: 20px">'.$formText_RemindersEmail_output.' - '.$formText_MissingAddressEmail_output.'</h1>
                                </td>
                            </tr>
                            <tr>
                                <td width="800" class="regular">
                                    <table width="800" border="1" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td>
                                                '.$formText_CustomerName_output.'
                                            </td>
                                            <td>
                                                '.$formText_InvoiceNumber_output.'
                                            </td>
                                            <td>
                                                '.$formText_Date_output.'
                                            </td>
                                            <td>
                                                '.$formText_DueDate_output.'
                                            </td>
                                            <td>
                                                '.$formText_OriginalAmount_output.'
                                            </td>
                                            <td>
                                                '.$formText_Balance_output.'
                                            </td>
                                        </tr>';
                                    foreach($missing_address_can_send_reminders as $manual_can_send_reminder) {
                                        $s_email_body .= '<tr>
                                            <td>
                                                '.$manual_can_send_reminder['customer_name'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['invoice_nr'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['date'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['due_date'].'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                            </td>
                                        </tr>';
                                    }
                    $s_email_body .=	'</table>
                                    </td>
                                </tr>
                            </table>';
                }
                if(count($small_amount_can_send_reminders) > 0) {
                    $s_email_body .='<table width="800">
                            <tr>
                                <td width="800" class="regular">
                                    <h1 class="title" style="font-size: 20px">'.$formText_RemindersEmail_output.' - '.$formText_SmallAmountEmail_output.'</h1>
                                </td>
                            </tr>
                            <tr>
                                <td width="800" class="regular">
                                    <table width="800" border="1" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td>
                                                '.$formText_CustomerName_output.'
                                            </td>
                                            <td>
                                                '.$formText_InvoiceNumber_output.'
                                            </td>
                                            <td>
                                                '.$formText_Date_output.'
                                            </td>
                                            <td>
                                                '.$formText_DueDate_output.'
                                            </td>
                                            <td>
                                                '.$formText_OriginalAmount_output.'
                                            </td>
                                            <td>
                                                '.$formText_Balance_output.'
                                            </td>
                                        </tr>';
                                    foreach($small_amount_can_send_reminders as $manual_can_send_reminder) {
                                        $s_email_body .= '<tr>
                                            <td>
                                                '.$manual_can_send_reminder['customer_name'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['invoice_nr'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['date'].'
                                            </td>
                                            <td>
                                                '.$manual_can_send_reminder['due_date'].'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                            </td>
                                            <td align="right">
                                                '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                            </td>
                                        </tr>';
                                    }
                    $s_email_body .=	'</table>
                                    </td>
                                </tr>
                            </table>';
                }
            } else {
                $s_email_body .='<table width="800">
                            <tr>
                                <td width="800" class="regular">
                                    <h1 class="title" style="font-size: 20px">'.$formText_NoInvoicesForReminderTodayEmail_output.'</h1>
                                </td>
                            </tr>
                </table>';
            }
            if(!$creditor['skip_reminder_go_directly_to_collecting']){
                if(count($automatic_move_to_collecting) > 0 || count($manual_move_to_collecting) > 0 || count($missing_address_move_to_collecting) > 0  || count($small_amount_move_to_collecting) > 0) {
                
                    if(count($automatic_move_to_collecting) > 0){
                        $s_email_body .='<table width="800">
                                    <tr>
                                        <td width="800" class="regular">
                                            <h1 class="title" style="font-size: 20px">'.$formText_CasesMovingToCollectingCaseEmail_output.' - '.$formText_AutomaticEmail_output.'</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="800" class="regular">
                                            <table width="800" border="1" cellspacing="0" cellpadding="2">
                                                <tr>
                                                    <td>
                                                        '.$formText_CustomerName_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_InvoiceNumber_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Date_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_DueDate_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_OriginalAmount_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Balance_output.'
                                                    </td>
                                                </tr>';
                                            foreach($automatic_move_to_collecting as $manual_can_send_reminder) {
                                                $s_email_body .= '<tr>
                                                    <td>
                                                        '.$manual_can_send_reminder['customer_name'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['invoice_nr'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['date'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['due_date'].'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                                    </td>
                                                </tr>';
                                            }
                        $s_email_body .=	'</table>
                                        </td>
                                    </tr>
                                </table>';
                    }
                    if(count($manual_move_to_collecting) > 0){
                        $s_email_body .='<table width="800">
                                    <tr>
                                        <td width="800" class="regular">
                                            <h1 class="title" style="font-size: 20px">'.$formText_CasesMovingToCollectingCaseEmail_output.' - '.$formText_ManualEmail_output.'</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="800" class="regular">
                                            <table width="800" border="1" cellspacing="0" cellpadding="2">
                                                <tr>
                                                    <td>
                                                        '.$formText_CustomerName_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_InvoiceNumber_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Date_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_DueDate_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_OriginalAmount_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Balance_output.'
                                                    </td>
                                                </tr>';
                                            foreach($manual_move_to_collecting as $manual_can_send_reminder) {
                                                $s_email_body .= '<tr>
                                                    <td>
                                                        '.$manual_can_send_reminder['customer_name'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['invoice_nr'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['date'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['due_date'].'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                                    </td>
                                                </tr>';
                                            }
                        $s_email_body .=	'</table>
                                        </td>
                                    </tr>
                                </table>';
                    }
                    if(count($missing_address_move_to_collecting) > 0){
                        $s_email_body .='<table width="800">
                                    <tr>
                                        <td width="800" class="regular">
                                            <h1 class="title" style="font-size: 20px">'.$formText_CasesMovingToCollectingCaseEmail_output.' - '.$formText_MissingAddressEmail_output.'</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="800" class="regular">
                                            <table width="800" border="1" cellspacing="0" cellpadding="2">
                                                <tr>
                                                    <td>
                                                        '.$formText_CustomerName_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_InvoiceNumber_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Date_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_DueDate_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_OriginalAmount_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Balance_output.'
                                                    </td>
                                                </tr>';
                                            foreach($missing_address_move_to_collecting as $manual_can_send_reminder) {
                                                $s_email_body .= '<tr>
                                                    <td>
                                                        '.$manual_can_send_reminder['customer_name'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['invoice_nr'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['date'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['due_date'].'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                                    </td>
                                                </tr>';
                                            }
                        $s_email_body .=	'</table>
                                        </td>
                                    </tr>
                                </table>';
                    }
                    if(count($small_amount_move_to_collecting) > 0){
                        $s_email_body .='<table width="800">
                                    <tr>
                                        <td width="800" class="regular">
                                            <h1 class="title" style="font-size: 20px">'.$formText_CasesMovingToCollectingCaseEmail_output.' - '.$formText_SmallAmountEmail_output.'</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="800" class="regular">
                                            <table width="800" border="1" cellspacing="0" cellpadding="2">
                                                <tr>
                                                    <td>
                                                        '.$formText_CustomerName_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_InvoiceNumber_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Date_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_DueDate_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_OriginalAmount_output.'
                                                    </td>
                                                    <td>
                                                        '.$formText_Balance_output.'
                                                    </td>
                                                </tr>';
                                            foreach($small_amount_move_to_collecting as $manual_can_send_reminder) {
                                                $s_email_body .= '<tr>
                                                    <td>
                                                        '.$manual_can_send_reminder['customer_name'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['invoice_nr'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['date'].'
                                                    </td>
                                                    <td>
                                                        '.$manual_can_send_reminder['due_date'].'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['amount'], 2, ",", " ").'
                                                    </td>
                                                    <td align="right">
                                                        '.number_format($manual_can_send_reminder['case_balance'], 2, ",", " ").'
                                                    </td>
                                                </tr>';
                                            }
                        $s_email_body .=	'</table>
                                        </td>
                                    </tr>
                                </table>';
                    }
                } else {
                }
            }

            $companyPhone = $creditor['companyphone'];
            $companyEmail = $creditor['companyEmail'];
            if($creditor['use_local_email_phone_for_reminder']) {
                $companyPhone = $creditor['local_phone'];
                $companyEmail = $creditor['local_email'];
            }

            $s_sql = "select * from sys_emailserverconfig order by default_server desc";
            $o_query = $o_main->db->query($s_sql);
            $v_email_server_config = $o_query ? $o_query->row_array() : array();

            $s_sql = "INSERT INTO sys_emailsend 
            (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) 
            VALUES (NULL, NOW(), ?, 2, NOW(), ?, ?, 0, 0, ?, 'creditor', '', 0, ?, ?, ?);";
            $o_main->db->query($s_sql, array($companyEmail, $creditor['companyname'], $collecting_system_settings['reminder_sender_email'], $creditor['id'], $s_email_subject, $s_email_body, $batch_id));
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
                $mail->addReplyTo($companyEmail, 'Noreply');
            }
            $mail->From		= $collecting_system_settings['reminder_sender_email'];
            $mail->FromName	= 'Noreply';
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
}
?>