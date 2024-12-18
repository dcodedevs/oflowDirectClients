<?php 
// error_reporting(E_ALL);
// ini_set("display_errors", 1);


if(!function_exists("get_current_balance")){
    function get_current_balance($transaction){
        global $o_main;

        $balance = $transaction['amount'];
        if($transaction['link_id'] != "") {
            $s_sql = "SELECT * FROM creditor_transactions          
            WHERE link_id is not null AND link_id = '".$o_main->db->escape_str($transaction["link_id"])."' 
            AND creditor_id = '".$o_main->db->escape_str($transaction["creditor_id"])."' AND id <> '".$o_main->db->escape_str($transaction["id"])."'
            AND ((system_type = 'Payment' OR system_type = 'CreditnoteCustomer') OR (system_type = 'InvoiceCustomer' AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%')))";
            $o_query = $o_main->db->query($s_sql);
            $connected_transactions = ($o_query ? $o_query->result_array() : array());
            foreach($connected_transactions as $connected_transaction){
                $balance += $connected_transaction['amount'];
            }
        }
        return $balance;
    }
}
if(!function_exists("get_current_fees_balance")){
    function get_current_fees_balance($transaction){
        global $o_main;
        $balance = 0;
        if($transaction['link_id'] != "") {
            $s_sql = "SELECT * FROM creditor_transactions          
            WHERE open = 1 AND link_id is not null AND link_id = '".$o_main->db->escape_str($transaction["link_id"])."' 
            AND creditor_id = '".$o_main->db->escape_str($transaction["creditor_id"])."' AND id <> '".$o_main->db->escape_str($transaction["id"])."'
            AND (system_type = 'InvoiceCustomer' AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%'))";
            $o_query = $o_main->db->query($s_sql);
            $connected_fees = ($o_query ? $o_query->result_array() : array());
            foreach($connected_fees as $connected_fee){
                $balance += $connected_fee['amount'];
            }
        }
        return $balance;
    }
}
if(!function_exists("update_tab_for_single_transaction")){
    function update_tab_for_single_transaction($open_transaction, $negative_amount_customers, 
    $collecting_cases_process_company_first_step, $collecting_cases_process_person_first_step, 
    $profile_values_company, $profile_values_person, $total_reminder_process_steps, $total_profile_values, 
    $collecting_cases_collecting_process_first_step, $creditor, $collecting_cases_collecting_process, 
    $source_id, $reminder_minimum_amounts, $collecting_system_settings, $total_reminder_processes, 
    $total_collecting_cases_collecting_processes, $tab_activated_creditor){
        global $o_main;
        global $variables;

        $nextStepName = "";
        $customerInvoiceEmail = "";
        $customerInvoicePhone = "";
        $customerName = "";
        $nextStepAction = 0;
        $next_step_date = "0000-00-00";
        $currentDueDate = "0000-00-00";
        
        $isCompany = false;
        $customer_type_collect_debitor = $open_transaction['customer_type_collect'];
        if($open_transaction['customer_type_collect_addition'] > 0){
            $customer_type_collect_debitor = $open_transaction['customer_type_collect_addition'] - 1;
        }	
        if($customer_type_collect_debitor == 0){
            $isCompany = true;
        }
        $days_after_due_date = 0;
        if($isCompany){
            $next_step = $collecting_cases_process_company_first_step;
            $profile_value = $profile_values_company[$next_step['id']];
        } else {
            $next_step = $collecting_cases_process_person_first_step;
            $profile_value = $profile_values_person[$next_step['id']];
        }
        if($open_transaction['reminder_profile_id'] > 0){        
            $s_sql = "SELECT ccps.id, ccps.days_after_due_date, ccps.name, ccps.sending_action FROM collecting_cases_process_steps ccps  
            JOIN collecting_cases_process stepProcess ON stepProcess.id = ccps.collecting_cases_process_id
            JOIN creditor_reminder_custom_profiles profile ON profile.reminder_process_id = stepProcess.id
            WHERE profile.id = '".$o_main->db->escape_str($open_transaction['reminder_profile_id'])."' ORDER BY ccps.sortnr ASC";
            $o_query = $o_main->db->query($s_sql);
            $selected_profile_first_step = ($o_query ? $o_query->row_array() : array());  
            if($selected_profile_first_step) {
                $next_step = $selected_profile_first_step;
            }
        }
        
        $currencyNameToCompare = "";

        if($open_transaction['currency'] != "LOCAL" && $open_transaction['currency'] != "") {
            $currencyNameToCompare = $open_transaction['currency'];
        } else {
            $currencyNameToCompare = $creditor['default_currency'];
        }    
        $minimum_amount_for_processing = intval($collecting_system_settings['default_reminder_minimum_amount_noncurrency']);
        foreach($reminder_minimum_amounts as $reminder_minimum_amount) {
            if(trim($currencyNameToCompare) != ""){
                if(mb_strtolower($reminder_minimum_amount['currency']) == mb_strtolower(trim($currencyNameToCompare))) {
                    $minimum_amount_for_processing = $reminder_minimum_amount['amount'];
                }
            } else {
                if(mb_strtolower($reminder_minimum_amount['currency']) == "nok") {
                    $minimum_amount_for_processing = $reminder_minimum_amount['amount'];
                }
            }
        }

        $customerInvoiceEmail = $open_transaction['invoiceEmail'];
        $customerName = $open_transaction['customerName'];
        $customerInvoicePhone = $open_transaction['phone'];
        $currentDueDate = $open_transaction['due_date'];
        $tab_status = 0;
        $b_show_in_first_tab = false;
        $b_show_in_last_tab = false;
        $b_next_step_is_oflow = false;

        $b_moving_to_collecting_case = false;
        $b_transaction_has_case = false;
        if($open_transaction['collectingcase_id']){
            $b_transaction_has_case = true;
        }
        if($open_transaction['open']){
            if($b_transaction_has_case){
                $next_step = $total_reminder_process_steps[$open_transaction['stepProcessId']][$open_transaction['nextStepId']];
                if(!$next_step){
                    $b_next_step_is_oflow = true;       
                    //ready for collecting
                    if(!$collecting_cases_collecting_process['with_warning']){
                        $b_show_in_last_tab = true;
                    }
                    if(!$collecting_cases_collecting_process['with_warning']){
                        $b_moving_to_collecting_case = true;
                    }
                    $current_process = $total_reminder_processes[$open_transaction['stepProcessId']];
                    if($current_process['collecting_process_move_to'] > 0) {
                        $collecting_process_move_to = $total_collecting_cases_collecting_processes[$current_process['collecting_process_move_to']];
                        $collecting_cases_collecting_process_first_step = $collecting_process_move_to['first_step'];
                    }
                    if($current_process['days_after_due_date_move_to_collecting']) {
                        $collecting_cases_collecting_process_first_step['days_after_due_date'] = $current_process['days_after_due_date_move_to_collecting'];
                    }
                } else {                
                    $profile_value = $total_profile_values[$open_transaction['reminder_profile_id']][$next_step['id']];
                }
            } else {
                //for transactions without case but creditor is skipping the reminder cases
                if($creditor['skip_reminder_go_directly_to_collecting']) {
                    //ready for collecting
                    $b_next_step_is_oflow = true;                
                    if(!$collecting_cases_collecting_process['with_warning']){
                        $b_moving_to_collecting_case = true;
                    }               
                    if(!$collecting_cases_collecting_process['with_warning']){
                        $b_show_in_last_tab = true;
                    }                
                }
                if(isset($total_profile_values[$open_transaction['reminder_profile_id']])){
                    $profile_value_key = array_key_first($total_profile_values[$open_transaction['reminder_profile_id']]);
                    $profile_value = $total_profile_values[$open_transaction['reminder_profile_id']][$profile_value_key];
                }
            }


            if(
                (!$b_moving_to_collecting_case && $open_transaction['choose_progress_of_reminderprocess'] == 3 || 
                ($open_transaction['choose_progress_of_reminderprocess'] == 0 
                && ($open_transaction['customer_choose_progress_of_reminderprocess'] == 3 || 
                    ($open_transaction['customer_choose_progress_of_reminderprocess'] == 0 && $creditor['choose_progress_of_reminderprocess'] == 2))
                )) || ($b_moving_to_collecting_case && ($open_transaction['choose_move_to_collecting_process'] == 3 || 
                ($open_transaction['choose_move_to_collecting_process'] == 0 
                && ($open_transaction['customer_choose_move_to_collecting_process'] == 3 || 
                    ($open_transaction['customer_choose_move_to_collecting_process'] == 0 && $creditor['choose_move_to_collecting_process'] == 2))
                )))
            ){
                //marked to not send
                $tab_status = 5;
            } else {    
                if($open_transaction['objectionId'] > 0){
                    //stopped with objection
                    $tab_status = 6;
                }  else {  
                    if($profile_value['days_after_due_date'] > 0){
                        $days_after_due_date = $profile_value['days_after_due_date'];
                    } else {
                        $days_after_due_date = $next_step['days_after_due_date'];
                    }
                    if($profile_value['sending_action'] > 0) {
                        $sending_action = $profile_value['sending_action'];
                    } else {
                        $sending_action = $next_step['sending_action'];
                    }
                    $nextStepName = $next_step['name'];
                    
                    $nextStepAction = $sending_action;
                    if($b_next_step_is_oflow) {
                        $nextStepName = $collecting_cases_collecting_process_first_step['name'];
                        $days_after_due_date = $collecting_cases_collecting_process_first_step['days_after_due_date'];
                        $nextStepAction = $collecting_cases_collecting_process_first_step['sending_action'];
                    }                
                    $next_step_date_time = strtotime($currentDueDate) + $days_after_due_date*86400;
                    $next_step_date = date("Y-m-d", $next_step_date_time);

                    if($next_step_date_time <= time()){
                        if(!$b_show_in_last_tab){
                            $b_show_in_first_tab = true;
                        }
                    } else {
                        //due date not here
                        $tab_status = 4;
                        $nocase = true;
                        if($open_transaction['collectingcase_id'] > 0) {
                            $nocase = false;
                        }
                        if($nocase) {
                            $tab_status = 13;
                        }
                    }
                }


                if($tab_status == 0){   
                    if($b_show_in_last_tab){
                        $invoiceEmailToCompare = preg_replace('/\xc2\xa0/', '', trim($customerInvoiceEmail));
                        if((str_replace(" ", "", $open_transaction['paStreet']) == '' || str_replace(" ", "", $open_transaction['paPostalNumber']) == '' || str_replace(" ", "", $open_transaction['paCity']) == '')){
                            //missing address
                            $tab_status = 9;
                        } else {                           
                            if(
                                $open_transaction['choose_move_to_collecting_process'] == 2 || 
                                ($open_transaction['choose_move_to_collecting_process'] == 0 
                                && ($open_transaction['customer_choose_move_to_collecting_process'] == 2 || 
                                    ($open_transaction['customer_choose_move_to_collecting_process'] == 0 && $creditor['choose_move_to_collecting_process'] == 1))
                                )
                            ){
                                //automatic
                                $tab_status = 8;
                            } else {
                                //manual
                                $tab_status = 7;
                            }
                        }
                    } else if($b_show_in_first_tab){
                        
                        $invoiceEmailToCompare = preg_replace('/\xc2\xa0/', '', trim($customerInvoiceEmail));
                        if(($nextStepAction == 1 
                        ||
                        ($nextStepAction == 2 && $invoiceEmailToCompare == "")
                        || 
                        ($nextStepAction == 4 && $invoiceEmailToCompare == "" && $open_transaction['phone'] == '')
                        ) 
                        && (str_replace(" ", "", $open_transaction['paStreet']) == '' || str_replace(" ", "", $open_transaction['paPostalNumber']) == '' || str_replace(" ", "", $open_transaction['paCity']) == '')){
                            //missing address
                            $tab_status = 3;
                        } else {                           
                            if($b_moving_to_collecting_case) {
                                if(
                                    $open_transaction['choose_move_to_collecting_process'] == 2 || 
                                    ($open_transaction['choose_move_to_collecting_process'] == 0 
                                    && ($open_transaction['customer_choose_move_to_collecting_process'] == 2 || 
                                        ($open_transaction['customer_choose_move_to_collecting_process'] == 0 && $creditor['choose_move_to_collecting_process'] == 1))
                                    )
                                ){
                                    //automatic
                                    $tab_status = 2;
                                } else {
                                    //manual
                                    $tab_status = 1;
                                }
                            } else {
                                if(
                                    $open_transaction['choose_progress_of_reminderprocess'] == 2 || 
                                    ($open_transaction['choose_progress_of_reminderprocess'] == 0 
                                    && ($open_transaction['customer_choose_progress_of_reminderprocess'] == 2 || 
                                        ($open_transaction['customer_choose_progress_of_reminderprocess'] == 0 && $creditor['choose_progress_of_reminderprocess'] == 1))
                                    )
                                ) {
                                    //automatic
                                    $tab_status = 2;
                                } else {
                                    //manual
                                    $tab_status = 1;
                                }
                            }
                        }
                    }
                }
            }
            $current_balance = 0;
            if($tab_status != 0){
                $current_balance = get_current_balance($open_transaction);
                if($current_balance <= 0){
                    $tab_status = 0;
                    if($b_transaction_has_case){
                        $negative_amount_customers[$open_transaction['customerId']] = $open_transaction['customerId'];    
                    } else {
                        $current_balance = 0;
                    }            
                } else {    
                    $fees_balance = get_current_fees_balance($open_transaction);
                    if(round($current_balance, 2) <= round($fees_balance, 2)) {
                        //case has only unpaid fees
                        $tab_status = 12;
                    } else {
                        if($tab_status == 7 || $tab_status == 8){
                            if($current_balance <= $collecting_system_settings['minimum_amount_move_to_collecting_company_case']) {
                                //case has too little amount to be moved
                                $tab_status = 11;
                            }
                        } else {
                            if($tab_status == 1 || $tab_status == 2){     
                                //case has too little amount to be processed                   
                                if($b_moving_to_collecting_case) {
                                    if($current_balance <= $collecting_system_settings['minimum_amount_move_to_collecting_company_case']) {
                                        $tab_status = 10;
                                    }
                                } else {
                                    if($current_balance <= $minimum_amount_for_processing) {
                                        $tab_status = 10;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $current_balance = get_current_balance($open_transaction);
        }
        if($tab_status > 0){
            $counter++;
        }
        $has_other_negative_transactions = 0;
        if($negative_amount_customers[$open_transaction['customerId']]){
            $has_other_negative_transactions = 1;
        }
        $case_choose_progress_of_reminderprocess = 0;
        $case_choose_move_to_collecting_process = 0;
        
        if($open_transaction['collectingcase_id'] > 0){
            $case_choose_progress_of_reminderprocess = $open_transaction['choose_progress_of_reminderprocess'];
            $case_choose_move_to_collecting_process = $open_transaction['choose_move_to_collecting_process'];
        }
        if($tab_activated_creditor && $open_transaction['tab_status'] != "" && $open_transaction['tab_status'] != $tab_status && $open_transaction['tab_status'] != 4) {
            $s_sql = "INSERT INTO creditor_transactions_status_log SET tab_status_from = ?, tab_status_to = ?, created = NOW(), creditor_transaction_id = ?, creditor_id = ?, source=?";
            $o_query = $o_main->db->query($s_sql, array($open_transaction['tab_status'], $tab_status, $open_transaction['id'], $open_transaction['creditor_id'], $source_id));
        }

        $s_sql = "UPDATE creditor_transactions SET tab_status = '".$o_main->db->escape_str($tab_status)."', 
        case_balance='".$o_main->db->escape_str($current_balance)."', 
        next_step_name ='".$o_main->db->escape_str($nextStepName)."', 
        next_step_is_collecting = '".($b_moving_to_collecting_case?1:0)."', 
        next_step_is_oflow = '".($b_next_step_is_oflow?1:0)."',
        next_step_action = '".$o_main->db->escape_str($nextStepAction)."', 
        customer_invoice_email='".$o_main->db->escape_str($customerInvoiceEmail)."',
        customer_name='".$o_main->db->escape_str($customerName)."',
        customer_invoice_phone='".$o_main->db->escape_str($customerInvoicePhone)."',
        current_due_date='".$o_main->db->escape_str($currentDueDate)."',
        next_step_date =  '".$o_main->db->escape_str($next_step_date)."',
        has_other_negative_amount_transactions = '".$o_main->db->escape_str($has_other_negative_transactions)."',
        case_choose_progress_of_reminderprocess = '".$o_main->db->escape_str($case_choose_progress_of_reminderprocess)."',
        case_choose_move_to_collecting_process = '".$o_main->db->escape_str($case_choose_move_to_collecting_process)."',
        case_profile_id = '".$o_main->db->escape_str($open_transaction['reminder_profile_id'])."',
        to_be_reordered = 0
        WHERE id = '".$o_main->db->escape_str($open_transaction['id'])."'";
        $o_query = $o_main->db->query($s_sql);


        
        return $negative_amount_customers;
    }
}
if(!function_exists("process_open_cases_for_tabs")){
    //$source_id:
    //1 - full reordering launched, 2 - syncing, 3- processing, 4-single transaction change, 5-customer setting change, 6  - profile changed,
    function process_open_cases_for_tabs($creditor_id, $source_id = 0, $ignore_restiction = false){
        global $o_main;
        global $variables;
        $s_sql = "SELECT * FROM creditor 
        WHERE id = '".$o_main->db->escape_str($creditor_id)."'";
        $o_query = $o_main->db->query($s_sql);
        $creditor = ($o_query ? $o_query->row_array() : array());
        if($creditor) {
            if($creditor['tab_reorder_status'] == 0){
                if($source_id == 1) {
                    $s_sql = "UPDATE creditor SET transaction_reorder_starttime = NOW() WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                }
                $tab_activated_creditor = true;
                // $creditor_active_ids = array(1041, 1031, 2000, 2759, 3969, 3995, 3663, 4234, 3148, 1006, 2365, 1943, 2921, 1233, 2215,2190,1405,2679,2109,2,3,2530, 4238, 4048, 4273, 4090, 3393);

                // if(in_array($creditor['id'], $creditor_active_ids)){
                //     $tab_activated_creditor = true;
                // }
                if(!$ignore_restiction && !$tab_activated_creditor) {
                    return;
                }             
                $s_sql = "UPDATE creditor SET tab_reorder_status = 1 WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($creditor['id']));

                $s_sql = "SELECT * FROM collecting_system_settings";
                $o_query = $o_main->db->query($s_sql);
                $collecting_system_settings = ($o_query ? $o_query->row_array() : array());

                $creditor_reminder_default_profile_id = $creditor['creditor_reminder_default_profile_id'];
                $creditor_reminder_default_profile_for_company_id = $creditor['creditor_reminder_default_profile_for_company_id'];
                
                $s_sql = "SELECT * FROM creditor_reminder_custom_profiles crcp         
                WHERE id = '".$o_main->db->escape_str($creditor_reminder_default_profile_id)."'";
                $o_query = $o_main->db->query($s_sql);
                $creditor_reminder_default_profile = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM creditor_reminder_custom_profiles crcp         
                WHERE id = '".$o_main->db->escape_str($creditor_reminder_default_profile_for_company_id)."'";
                $o_query = $o_main->db->query($s_sql);
                $creditor_reminder_default_profile_for_company = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_process ccp         
                WHERE id = '".$o_main->db->escape_str($creditor_reminder_default_profile['reminder_process_id'])."'";
                $o_query = $o_main->db->query($s_sql);
                $collecting_cases_process_person = ($o_query ? $o_query->row_array() : array());
                
                $s_sql = "SELECT * FROM collecting_cases_process ccp         
                WHERE id = '".$o_main->db->escape_str($creditor_reminder_default_profile_for_company['reminder_process_id'])."'";
                $o_query = $o_main->db->query($s_sql);
                $collecting_cases_process_company = ($o_query ? $o_query->row_array() : array());
                
                $s_sql = "SELECT ccps.id, ccps.days_after_due_date, ccps.name, ccps.sending_action FROM collecting_cases_process_steps ccps         
                WHERE collecting_cases_process_id = '".$o_main->db->escape_str($collecting_cases_process_person['id'])."' ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql);
                $collecting_cases_process_person_first_step = ($o_query ? $o_query->row_array() : array());
                
                $s_sql = "SELECT ccps.id, ccps.days_after_due_date, ccps.name, ccps.sending_action FROM collecting_cases_process_steps ccps         
                WHERE collecting_cases_process_id = '".$o_main->db->escape_str($collecting_cases_process_company['id'])."' ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql);
                $collecting_cases_process_company_first_step = ($o_query ? $o_query->row_array() : array());            
                
                $s_sql = "SELECT * FROM reminder_minimum_amount ORDER BY currency ASC";
                $o_query = $o_main->db->query($s_sql);
                $reminder_minimum_amounts = $o_query ? $o_query->result_array() : array();

                $s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
                $o_query = $o_main->db->query($s_sql, array($creditor_reminder_default_profile['id']));
                $unprocessed_profile_values_person = $o_query ? $o_query->result_array() : array();

                $s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
                $o_query = $o_main->db->query($s_sql, array($creditor_reminder_default_profile_for_company['id']));
                $unprocessed_profile_values_company = $o_query ? $o_query->result_array() : array();

                $profile_values_person = array();
                foreach($unprocessed_profile_values_person as $unprocessed_profile_value) {
                    $profile_values_person[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
                }
                $profile_values_company = array();
                foreach($unprocessed_profile_values_company as $unprocessed_profile_value) {
                    $profile_values_company[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
                }
                
                    
                $s_sql = "SELECT creditor_reminder_custom_profile_values.* FROM creditor_reminder_custom_profile_values 
                JOIN creditor_reminder_custom_profiles on creditor_reminder_custom_profiles.id = creditor_reminder_custom_profile_values.creditor_reminder_custom_profile_id  
                WHERE creditor_reminder_custom_profiles.creditor_id = ?";
                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                $unprocessed_profile_values = $o_query ? $o_query->result_array() : array();
                $total_profile_values = array();
                foreach($unprocessed_profile_values as $unprocessed_profile_value) {
                    $total_profile_values[$unprocessed_profile_value['creditor_reminder_custom_profile_id']][$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
                }
                $total_reminder_process_steps = array();
                $total_reminder_processes = array();
                
                $s_sql = "SELECT * FROM collecting_cases_process ccp WHERE content_status < 2";
                $o_query = $o_main->db->query($s_sql);
                $reminder_processes = ($o_query ? $o_query->result_array() : array());
                foreach($reminder_processes as $reminder_process){            
                    $s_sql = "SELECT ccps.id, ccps.days_after_due_date, ccps.name, ccps.sending_action FROM collecting_cases_process_steps ccps         
                    WHERE collecting_cases_process_id = '".$o_main->db->escape_str($reminder_process['id'])."' ORDER BY sortnr ASC";
                    $o_query = $o_main->db->query($s_sql);
                    $reminder_process_steps = ($o_query ? $o_query->result_array() : array());
                    $processed_reminder_process_steps = array();
                    foreach($reminder_process_steps as $reminder_process_step){
                        $processed_reminder_process_steps[$reminder_process_step['id']] = $reminder_process_step;
                    }
                    $total_reminder_process_steps[$reminder_process['id']]=$processed_reminder_process_steps;
                    $total_reminder_processes[$reminder_process['id']] = $reminder_process;
                }
                if($creditor['collecting_process_to_move_from_reminder'] == 0) {
                    $s_sql = "SELECT * FROM collecting_cases_collecting_process          
                    WHERE id = '".$o_main->db->escape_str($collecting_system_settings['default_collecting_process_to_move_from_reminder'])."'";
                    $o_query = $o_main->db->query($s_sql);
                    $collecting_cases_collecting_process = ($o_query ? $o_query->row_array() : array());
                } else {
                    $s_sql = "SELECT * FROM collecting_cases_collecting_process          
                    WHERE id = '".$o_main->db->escape_str($creditor['collecting_process_to_move_from_reminder'])."'";
                    $o_query = $o_main->db->query($s_sql);
                    $collecting_cases_collecting_process = ($o_query ? $o_query->row_array() : array());
                }
                $s_sql = "SELECT ccps.id, ccps.days_after_due_date, ccps.name, ccps.sending_action FROM collecting_cases_collecting_process_steps ccps         
                WHERE collecting_cases_collecting_process_id = '".$o_main->db->escape_str($collecting_cases_collecting_process['id'])."' ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql);
                $collecting_cases_collecting_process_first_step = ($o_query ? $o_query->row_array() : array());

                $total_collecting_cases_collecting_processes = array();
                $s_sql = "SELECT * FROM collecting_cases_collecting_process";
                $o_query = $o_main->db->query($s_sql);
                $all_collecting_cases_collecting_processes = ($o_query ? $o_query->row_array() : array());
                foreach($all_collecting_cases_collecting_processes as $all_collecting_cases_collecting_process){                
                    $s_sql = "SELECT ccps.id, ccps.days_after_due_date, ccps.name, ccps.sending_action FROM collecting_cases_collecting_process_steps ccps         
                    WHERE collecting_cases_collecting_process_id = '".$o_main->db->escape_str($all_collecting_cases_collecting_process['id'])."' ORDER BY sortnr ASC";
                    $o_query = $o_main->db->query($s_sql);
                    $first_step = ($o_query ? $o_query->row_array() : array());
                    $all_collecting_cases_collecting_process['first_step'] = $first_step;
                    $total_collecting_cases_collecting_processes[$all_collecting_cases_collecting_process['id']] = $all_collecting_cases_collecting_process;
                }
                if(($collecting_cases_process_company_first_step && $collecting_cases_process_person_first_step) || $creditor['skip_reminder_go_directly_to_collecting']){
                    if($creditor['reminder_only_from_invoice_nr'] > 0) {
                        $creditor_sql = " AND ct.invoice_nr >= ".intval($creditor['reminder_only_from_invoice_nr']);
                    }
                    $s_sql = "SELECT ct.id, ct.choose_progress_of_reminderprocess, ct.choose_move_to_collecting_process, ct.due_date, ct.link_id, ct.creditor_id, ct.amount, ct.collectingcase_id,
                    c.customer_type_collect, c.customer_type_collect_addition, 
                    c.choose_progress_of_reminderprocess as customer_choose_progress_of_reminderprocess,
                    c.choose_move_to_collecting_process as customer_choose_move_to_collecting_process,
                    c.invoiceEmail, c.phone,  CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName,
                    c.paStreet, c.paPostalNumber, c.paCity,
                    c.id as customerId, ct.tab_status, ct.currency,ct.open,
                    IFNULL(ct.reminder_profile_id, c.creditor_reminder_profile_id) as reminder_profile_id                    
                    FROM creditor_transactions ct         
                    LEFT JOIN customer c ON c.creditor_customer_id = ct.external_customer_id AND c.creditor_id = ct.creditor_id
                    WHERE ct.content_status < 2 AND ct.open = 1 AND (ct.system_type='InvoiceCustomer') 
                    AND (ct.collecting_company_case_id IS NULL or ct.collecting_company_case_id = 0) 
                    AND (ct.collectingcase_id IS NULL or ct.collectingcase_id = 0) 
                    AND ct.creditor_id = '".$o_main->db->escape_str($creditor["id"])."' AND ct.due_date is not null
                    AND (ct.comment is NULL OR ct.comment = '' OR ct.comment NOT LIKE '%\_%') AND ct.to_be_reordered = 1
                    ".$creditor_sql."
                    GROUP BY ct.id";
                    $o_query = $o_main->db->query($s_sql);
                    $open_transactions_without_case = ($o_query ? $o_query->result_array() : array());
                    
                    $s_sql = "SELECT ct.id, IFNULL(cc.choose_progress_of_reminderprocess, ct.choose_progress_of_reminderprocess) as choose_progress_of_reminderprocess, 
                    IFNULL(cc.choose_move_to_collecting_process, ct.choose_move_to_collecting_process) as choose_move_to_collecting_process, 
                    IF(cc.due_date > ct.due_date, cc.due_date, ct.due_date) AS due_date, ct.link_id, ct.creditor_id, ct.amount, ct.collectingcase_id, cc.reminder_profile_id, cc.collecting_cases_process_step_id, nextStep.id as nextStepId, stepProcess.id as stepProcessId, obj.id as objectionId,
                    c.customer_type_collect, c.customer_type_collect_addition, 
                    c.choose_progress_of_reminderprocess as customer_choose_progress_of_reminderprocess,
                    c.choose_move_to_collecting_process as customer_choose_move_to_collecting_process,
                    c.invoiceEmail, c.phone, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName,
                    c.paStreet, c.paPostalNumber, c.paCity,
                    c.id as customerId, ct.tab_status, ct.currency,ct.open
                    FROM creditor_transactions ct         
                    JOIN customer c ON c.creditor_customer_id = ct.external_customer_id AND c.creditor_id = ct.creditor_id
                    JOIN collecting_cases cc ON cc.id = ct.collectingcase_id AND cc.debitor_id = c.id
                    JOIN creditor_reminder_custom_profiles profile ON profile.id = cc.reminder_profile_id
                    JOIN collecting_cases_process stepProcess ON stepProcess.id = profile.reminder_process_id
                    LEFT JOIN collecting_cases_process_steps step2 ON step2.id = cc.collecting_cases_process_step_id AND step2.collecting_cases_process_id = profile.reminder_process_id
                    LEFT JOIN collecting_cases_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_process_id = profile.reminder_process_id
                    LEFT JOIN collecting_cases_objection obj ON obj.collecting_case_id = cc.id AND (obj.objection_closed_date = '0000-00-00' or obj.objection_closed_date is null)
                    WHERE ct.content_status < 2 AND (ct.system_type='InvoiceCustomer') 
                    AND (ct.collecting_company_case_id IS NULL or ct.collecting_company_case_id = 0) 
                    AND ct.creditor_id = '".$o_main->db->escape_str($creditor["id"])."' AND ct.due_date is not null
                    AND (ct.comment is NULL OR ct.comment = '' OR ct.comment NOT LIKE '%\_%') AND ct.to_be_reordered = 1
                    ".$creditor_sql."
                    GROUP BY ct.id";
                    $o_query = $o_main->db->query($s_sql);
                    $open_transactions_with_case = ($o_query ? $o_query->result_array() : array());
                    $open_transactions = array_merge($open_transactions_without_case, $open_transactions_with_case);
                    $negative_amount_customers = array();

                    
                    foreach($open_transactions as $open_transaction) {
                        $negative_amount_customers = update_tab_for_single_transaction($open_transaction, $negative_amount_customers, $collecting_cases_process_company_first_step, $collecting_cases_process_person_first_step, $profile_values_company, $profile_values_person, $total_reminder_process_steps, $total_profile_values, $collecting_cases_collecting_process_first_step, $creditor, $collecting_cases_collecting_process,$source_id, $reminder_minimum_amounts, $collecting_system_settings, $total_reminder_processes, $total_collecting_cases_collecting_processes, $tab_activated_creditor);
                    }
                }
                if($creditor['reminder_only_from_invoice_nr'] > 0) {
                    $s_sql = "SELECT id FROM creditor_transactions 
                    WHERE creditor_id = ? AND invoice_nr < ".intval($creditor['reminder_only_from_invoice_nr'])." AND tab_status <> 5";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $transactionsToUpdateCount = $o_query ? $o_query->num_rows() : 0;
                    // var_dump($o_main->db->last_query());
                    if($transactionsToUpdateCount > 0) {
                        $s_sql = "UPDATE creditor_transactions SET 
                        tab_status = '5',
                        to_be_reordered = 0
                        WHERE creditor_id = ? AND invoice_nr < ".intval($creditor['reminder_only_from_invoice_nr'])." ";
                        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    }
                }
                $update_creditor_sql = "";
                $s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND tab_status = 12";
                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                $feesonly_case_count = $o_query ? $o_query->num_rows() : 0;
                if($feesonly_case_count > 0){
                    $update_creditor_sql .= ", has_mainclaim_payed = 1";
                }
                $s_sql = "UPDATE creditor SET transaction_reorder_endtime = NOW(), tab_reorder_status = 0".$update_creditor_sql." WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
            }
        }
    }
}
?>