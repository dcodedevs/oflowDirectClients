<?php 
function get_income_report($creditor_id){    
    global $o_main;
    $total_result = array();
    $step1_ids = array(28, 33, 16, 19, 22, 32);
    $step2_ids = array(8, 29, 1, 4, 17, 20, 23, 6, 12, 14, 10, 3);
    $step3_ids = array(9, 30, 31, 2, 25, 26, 27, 5, 18, 21, 24, 7, 13, 15, 11);
    $s_sql = "SELECT cc.* FROM collecting_cases cc
    WHERE cc.creditor_id = ? AND IFNULL(cc.created, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' ORDER BY cc.created ASC";
    $o_query = $o_main->db->query($s_sql, array($creditor_id));
    $first_collecting_case =$o_query ? $o_query->row_array() : array();
    if($first_collecting_case) { 

        $first_case_date_time = new DateTime($first_collecting_case['created']);
        $current_time = new DateTime(date("Y-m-d"));
        $interval = $current_time->diff($first_case_date_time);
        $month_back = ($interval->format('%y') * 12) + $interval->format('%m');
        $month_start_time = strtotime("-".$month_back." months");

        for($x=0; $x<$month_back; $x++){
            $month_time = strtotime("+".$x." months", $month_start_time);
            $month_start = date("Y-m-01", $month_time);
            $month_end = date("Y-m-t", $month_time);
            $step1_count = 0;
            $step2_count = 0;
            $step3_count = 0;  
            $moved_to_collecting_count = 0;
            $open_cases_count = 0;
            $mainclaim_payed = 0;
            $mainclaim_notpayed = 0;
            $fees_payed = 0;
            $interest_payed = 0;
            $open_cases_balance = 0;
            $balance = 0;

            $s_sql = "SELECT cc.*, ct.open, ct.case_balance, ct.link_id, ct.amount as transaction_amount FROM collecting_cases cc 
            JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
            WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ?";
            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor_id));
            $collectingCasesStartedInPeriod =$o_query ? $o_query->result_array() : array();
            $original_main_claim_sum = 0;
            $link_ids = array();
            foreach($collectingCasesStartedInPeriod as $collectingCase){
                $original_main_claim_sum += $collectingCase['original_main_claim'];
                
                $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
                $v_claim_letters = ($o_query ? $o_query->result_array() : array());
                foreach($v_claim_letters as $v_claim_letter) {
                    if(!$v_claim_letter['rest_note']){
                        if(in_array($v_claim_letter['step_id'], $step1_ids)){
                            $step1_count++;
                        }
                        if(in_array($v_claim_letter['step_id'], $step2_ids)){
                            $step2_count++;
                        }
                        if(in_array($v_claim_letter['step_id'], $step3_ids)){
                            $step3_count++;
                        }
                    }
                }
                $moved_to_collecting_date = "0000-00-00";
                if($collectingCase['sub_status'] == 5 && $collectingCase['stopped_date'] != "0000-00-00") {
                    $moved_to_collecting_count++;
                    $moved_to_collecting_date = $collectingCase['stopped_date'];
                }
                if($collectingCase['open']){
                    $open_cases_count++;
                    $open_cases_balance += $collectingCase['case_balance'];
                }
                $interest_amount = 0;                            
                $fees_amount = 0;
                $balance += $collectingCase['transaction_amount'];

                if($collectingCase['link_id'] != "") {
                    if(!in_array($collectingCase['link_id'], $link_ids)){
                        
                        $s_sql = "SELECT * FROM creditor_transactions WHERE (system_type='InvoiceCustomer' OR system_type='CreditnoteCustomer') AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) ORDER BY created DESC";
                        $o_query = $o_main->db->query($s_sql, array($collectingCase['link_id'], $collectingCase['creditor_id']));
                        $connected_transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($connected_transactions as $connected_transaction){          
                            $commentArray = explode("_",$connected_transaction['comment']);
                            $transactionType = "";
                            if($commentArray[2] == "interest") {
                                $transactionType = "interest";
                            } else if($commentArray[2] == "reminderFee") {
                                $transactionType = "reminderFee";
                            } else if($commentArray[0] == "Rente") {
                                $transactionType = "interest";
                            }                             
                            if($transactionType == "reminderFee") {	
                                $fees_amount += $connected_transaction['amount'];
                            } else if($transactionType == "interest") {	
                                $interest_amount += $connected_transaction['amount'];
                            }

                            $balance += $connected_transaction['amount'];
                        } 
                        // $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
                        // $o_query = $o_main->db->query($s_sql, array($collectingCase['link_id'], $collectingCase['creditor_id']));
                        // $claim_transactions = ($o_query ? $o_query->result_array() : array());
                        // foreach($claim_transactions as $claim_transaction) {
                            
                        // }
                        $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($collectingCase['link_id'], $collectingCase['creditor_id']));
                        $invoice_payments = ($o_query ? $o_query->result_array() : array());
                        
                        $total_payed = 0;
                        foreach($invoice_payments as $invoice_payment) {
                            if($moved_to_collecting_date == "0000-00-00" || strtotime($moved_to_collecting_count) >= strtotime($invoice_payment['created'])){    
                                $total_payed += abs($invoice_payment['amount']);
                                $balance += $invoice_payment['amount'];
                            }
                        }
                        if($total_payed > 0) {
                            if($total_payed > $collectingCase['original_main_claim']) {
                                $mainclaim_payed += $collectingCase['original_main_claim'];
                                $total_payed-= $collectingCase['original_main_claim'];
                            } else {
                                $mainclaim_payed += $total_payed;
                                $mainclaim_notpayed +=  $collectingCase['original_main_claim'] - $total_payed;                           
                                $total_payed = 0;
                            }
                            if($total_payed > $fees_amount){
                                $fees_payed += $fees_amount;
                                $total_payed-= $fees_amount;
                            } else {
                                $fees_payed += $total_payed;                                
                                $total_payed = 0;
                            }                            
                            if($total_payed > $interest_amount){
                                $interest_payed += $interest_amount;
                                $total_payed-= $interest_amount;
                            } else {
                                $interest_payed += $total_payed;                                
                                $total_payed = 0;
                            }
                        } else {
                            $mainclaim_notpayed += $collectingCase['original_main_claim'];
                        }
                        $link_ids[] = $collectingCase['link_id'];
                    }
                }
            }

            $mainclaim_payed_percentage = 0;
            if($original_main_claim_sum > 0){
                $mainclaim_payed_percentage = round($mainclaim_payed/$original_main_claim_sum*100, 2);
            }

            $single_array = array(
                'original_main_claim_sum'=>$original_main_claim_sum,
                'cases_started_in_period_count'=>count($collectingCasesStartedInPeriod),
                'step1_count'=>$step1_count,
                'step2_count'=>$step2_count,
                'step3_count'=>$step3_count,
                'moved_to_collecting_count'=>$moved_to_collecting_count,
                'mainclaim_payed'=>$mainclaim_payed,
                'interest_payed'=>$interest_payed,
                'fees_payed'=>$fees_payed,
                'interest_payed'=>$interest_payed,
                'mainclaim_notpayed'=>$mainclaim_notpayed,
                'open_cases_balance'=>$open_cases_balance,
                'open_cases_count'=>$open_cases_count,
                'mainclaim_payed_percentage'=>$mainclaim_payed_percentage,
            );
            $total_result[date("M Y", $month_time)] = $single_array;
        }
    }
    return $total_result;
}

function get_collecting_income_report($creditor_id){ 
    global $o_main;
    $total_result = array();
    $s_sql = "SELECT cc.* FROM collecting_company_cases cc
    WHERE cc.creditor_id = ? AND IFNULL(cc.created, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' ORDER BY cc.created ASC";
    $o_query = $o_main->db->query($s_sql, array($creditor_id));
    $first_collecting_case =$o_query ? $o_query->row_array() : array();
    if($first_collecting_case) { 

        $first_case_date_time = new DateTime($first_collecting_case['created']);
        $current_time = new DateTime(date("Y-m-d"));
        $interval = $current_time->diff($first_case_date_time);
        $month_back = ($interval->format('%y') * 12) + $interval->format('%m');
        $month_start_time = strtotime("-".$month_back." months");
        for($x=0; $x<$month_back; $x++){
            $month_time = strtotime("+".$x." months", $month_start_time);
            $month_start = date("Y-m-01", $month_time);
            $month_end = date("Y-m-t", $month_time);
            $step1_count = 0;
            $step2_count = 0;
            $step3_count = 0;  
            $moved_to_collecting_count = 0;
            $open_cases_count = 0;
            $mainclaim_payed = 0;
            $mainclaim_notpayed = 0;
            $fees_payed = 0;
            $interest_payed = 0;
            $open_cases_balance = 0;
            $balance = 0;
            $original_main_claim_sum = 0;

            $s_sql = "SELECT ccc.*
            FROM collecting_company_cases ccc 
            WHERE 
            (IF(IFNULL(ccc.warning_case_created_date, '0000-00-00') = '0000-00-00', ccc.collecting_case_created_date, ccc.warning_case_created_date) >= ? 
            AND IF(IFNULL(ccc.warning_case_created_date, '0000-00-00') = '0000-00-00', ccc.collecting_case_created_date, ccc.warning_case_created_date) <= ?)
            AND ccc.creditor_id = ?";
            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor_id));
            $collecting_company_cases =$o_query ? $o_query->result_array() : array();
            foreach($collecting_company_cases as $collecting_company_case){
                $s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ?";
                $o_query = $o_main->db->query($s_sql, array($collecting_company_case['id']));
                $claimlines = ($o_query ? $o_query->result_array() : array());
                foreach($claimlines as $claimline){
                    if($claimline['claim_type'] == 1){
                        $original_main_claim_sum+= $claimline['amount'];
                    } else {
                        if($claim['payment_after_closed']) {
                            $original_main_claim_sum+= $claimline['amount'];
                        }
                    }
                }
                if($collecting_company_case['case_closed_date'] == "0000-00-00" || $collecting_company_case['case_closed_date'] == ""){
                    $open_cases_count++;
                }
                $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_company_case['id']));
                $payments = ($o_query ? $o_query->result_array() : array());
                foreach($payments as $payment) {
                    // $s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE cmt.bookaccount_id = '1' AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
                    // $o_query = $o_main->db->query($s_sql);
                    // $transactions = ($o_query ? $o_query->result_array() : array());
                    // foreach($transactions as $transaction){
                    //     $balance -= $transaction['amount'];
                    //     $checksum -= $transaction['amount'];
                    //     $ledgerChecksum+= $transaction['amount'];
                    // }
                    // $s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '15' OR cmt.bookaccount_id = '16' OR cmt.bookaccount_id = '22') AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
                    // $o_query = $o_main->db->query($s_sql);
                    // $ledger_transactions = ($o_query ? $o_query->result_array() : array());
                    // foreach($ledger_transactions as $transaction){
                    //     $ledgerChecksum+= $transaction['amount'];
                    // }

                    //mainclaim
                    $s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '20' OR cmt.bookaccount_id = 19) AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
                    $o_query = $o_main->db->query($s_sql);
                    $ledger_transactions = ($o_query ? $o_query->result_array() : array());
                    foreach($ledger_transactions as $transaction){
                        $mainclaim_payed += abs($transaction['amount']);
                    }
                }
            }
            $mainclaim_notpayed = $original_main_claim_sum - $mainclaim_payed;

            $mainclaim_payed_percentage = 0;
            if($original_main_claim_sum > 0){
                $mainclaim_payed_percentage = round($mainclaim_payed/$original_main_claim_sum*100, 2);
            }
            
            $single_array = array(
                'original_main_claim_sum'=>$original_main_claim_sum,
                'collecting_company_cases_count'=>count($collecting_company_cases),
                'open_cases_count'=>$open_cases_count,
                'mainclaim_payed'=>$mainclaim_payed,
                'mainclaim_notpayed'=>$mainclaim_notpayed,
                'mainclaim_payed_percentage'=>$mainclaim_payed_percentage,
            );
            $total_result[date("M Y", $month_time)] = $single_array;            
        }
    }
    return $total_result;
}
?>