<?php 
if(!function_exists("get_collecting_case_report_data")){
    function get_collecting_case_report_data($o_main, $report_year, $report_period, $info = '') {
        $s_period_start = $report_year.'-01-01';
        $s_period_end = $report_year.(1==$report_period?'-12-01':'-06-01');
        $s_period_end = date("Y-m-t", strtotime($s_period_end));

        $sql_content_status = " AND IFNULL(cc.content_status, 0) = 0";
        $sql_active = " AND (IFNULL(cc.case_closed_date, '0000-00-00') = '0000-00-00' OR cc.case_closed_date > '".$o_main->db->escape_str($s_period_start)."')";
        $sql_active_with_closed = " AND (IFNULL(cc.case_closed_date, '0000-00-00') = '0000-00-00' OR cc.case_closed_date > '".$o_main->db->escape_str($s_period_end)."')";
        $sql_reminder_level = " AND IFNULL(cc.collecting_case_created_date, '0000-00-00') = '0000-00-00'";
        $sql_collecting_level = " AND IFNULL(cc.collecting_case_created_date, '0000-00-00') <> '0000-00-00'";
        $sql_reminder_level_active = " AND (cc.warning_case_created_date >= '".$o_main->db->escape_str($s_period_start)."'
        AND cc.warning_case_created_date <= '".$o_main->db->escape_str($s_period_end)."')";
        $sql_collecting_level_active = " AND (cc.collecting_case_created_date >= '".$o_main->db->escape_str($s_period_start)."'
        AND cc.collecting_case_created_date <= '".$o_main->db->escape_str($s_period_end)."')";
        $sql_both_level_active = " AND IF(IFNULL(cc.collecting_case_created_date, '0000-00-00') = '0000-00-00', cc.warning_case_created_date, cc.collecting_case_created_date) >= '".$o_main->db->escape_str($s_period_start)."'
        AND IF(IFNULL(cc.collecting_case_created_date, '0000-00-00') = '0000-00-00', cc.warning_case_created_date, cc.collecting_case_created_date) <= '".$o_main->db->escape_str($s_period_end)."'";
        
        $sql_new_cases_both_levels = " AND IF(IFNULL(cc.warning_case_created_date, '0000-00-00') = '0000-00-00', cc.collecting_case_created_date, cc.warning_case_created_date) >= '".$o_main->db->escape_str($s_period_start)."'
        AND IF(IFNULL(cc.warning_case_created_date, '0000-00-00') = '0000-00-00', cc.collecting_case_created_date, cc.warning_case_created_date) <= '".$o_main->db->escape_str($s_period_end)."'";
       
        $sql_company = " AND IF(IFNULL(c.customer_type_for_collecting_cases, 0)=0, IF(IFNULL(c.customer_type_collect_addition, 0) > 0, c.customer_type_collect_addition - 1, IFNULL(c.customer_type_collect, 0)), c.customer_type_for_collecting_cases - 1) = 0";
        $sql_person = " AND IF(IFNULL(c.customer_type_for_collecting_cases, 0)=0, IF(IFNULL(c.customer_type_collect_addition, 0) > 0, c.customer_type_collect_addition - 1, IFNULL(c.customer_type_collect, 0)), c.customer_type_for_collecting_cases - 1) = 1";
        
        if($info == '' || $info == 'value_1') {
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases AS cc
            WHERE IFNULL(cc.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND cc.warning_case_created_date < '".$o_main->db->escape_str($s_period_start)."'
            ".$sql_reminder_level.$sql_active.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfActiveCasesInReminderlevelAtBeginningOfYear_Output
            $v_count['value_1'] = $v_row['cnt'];
            if($info != ''){        
                $s_sql = "SELECT * FROM collecting_company_cases AS cc
                WHERE IFNULL(cc.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND cc.warning_case_created_date < '".$o_main->db->escape_str($s_period_start)."'
                ".$sql_reminder_level.$sql_active.$sql_content_status;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_1_info'] = $results;
            }
        }
        if($info == '' || $info == 'value_2'){
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases AS cc
            WHERE cc.collecting_case_created_date < '".$o_main->db->escape_str($s_period_start)."'
            ".$sql_collecting_level.$sql_active.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfActiveCasesInCollectinglevelAtBeginningOfYear_Output
            $v_count['value_2'] = $v_row['cnt'];
            if($info != ''){        
                $s_sql = "SELECT * FROM collecting_company_cases AS cc
                WHERE cc.collecting_case_created_date < '".$o_main->db->escape_str($s_period_start)."'
                ".$sql_collecting_level.$sql_active.$sql_content_status;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_2_info'] = $results;
            }
        }

        if($info == '' || $info == 'value_3') { 
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases AS cc WHERE
            1=1 ".$sql_both_level_active.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfNewCasesInBothLevelsSoFarThisYear_Output
            $v_count['value_3'] = $v_row['cnt'];
            if($info != ''){        
                $s_sql = "SELECT * FROM collecting_company_cases AS cc WHERE
                1=1 ".$sql_both_level_active.$sql_content_status;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_3_info'] = $results;
            }
        }
        if($info == '' || $info == 'value_4') { 
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases AS cc WHERE
            cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."'
            AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."'
            ".$sql_reminder_level.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfClosedCasesInReminderlevelSoFarThisYear_Output
            $v_count['value_4'] = $v_row['cnt'];
            if($info != ''){                
                $s_sql = "SELECT * FROM collecting_company_cases AS cc WHERE
                cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."'
                AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."'
                ".$sql_reminder_level.$sql_content_status;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_4_info'] = $results;
            }
        }
        if($info == '' || $info == 'value_5') { 
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases AS cc WHERE
            cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."'
            AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."'
            ".$sql_collecting_level.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfClosedCasesInCollectinglevelSoFarThisYear_Output
            $v_count['value_5'] = $v_row['cnt'];
            
            if($info != ''){                
                $s_sql = "SELECT * FROM collecting_company_cases AS cc WHERE
                cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."'
                AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."'
                ".$sql_collecting_level.$sql_content_status;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_5_info'] = $results;
            }
        }

        // $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases AS cc WHERE 1=1 ".$sql_both_level_active.
        // $sql_active_with_closed.$sql_content_status;
        // $o_query = $o_main->db->query($s_sql);
        // $v_row = $o_query ? $o_query->row_array() : array();
        
        if($info == '' || $info == 'value_6' || $info == 'value_7' || $info == 'value_8') { 
            $s_sql = "SELECT *
            FROM collecting_company_cases AS cc WHERE 1=1 ".
            $sql_active_with_closed.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $active_cases = $o_query ? $o_query->result_array() : array();
            //formText_NumberOfActiveCasesInBothLevels_Output
            $v_count['value_6'] = count($active_cases);
            if($info != ''){  
                $v_count['value_6_info'] = $active_cases;
            }

            $active_cases_main_claim = 0;
            $active_cases_total_claim = 0;
            $processes_active_cases = array();
            foreach($active_cases as $active_case) {
                $current_case_main_claim = 0;
                $current_case_total_claim = 0;
                $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ?
                AND date <= '".$o_main->db->escape_str($s_period_end)."'
                ORDER BY created ASC";
                $o_query = $o_main->db->query($s_sql, array($active_case['id']));
                $payments = ($o_query ? $o_query->result_array() : array());
                
                $s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
                collecting_company_cases_claim_lines AS ccccl
                JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
                WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($active_case['id'])."' AND IFNULL(cccltb.not_include_in_claim, 0) = 0";
                $o_query = $o_main->db->query($s_sql);
                $claimlines = $o_query ? $o_query->result_array() : array();
                $mainclaimline = array();
                foreach($claimlines as $claimline) {
                    if($claimline['date'] == '0000-00-00' OR strtotime($claimline['date']) <= strtotime($s_period_end) ){
                        if($claimline['claim_type'] == 15 || $claimline['claim_type'] == 16 || $claimline['claim_type'] == 18) {
                            if(!$claimline['payment_after_closed']){
                                $active_cases_main_claim += $claimline['amount'];
                                $active_cases_total_claim += $claimline['amount'];
                                $current_case_main_claim += $claimline['amount'];
                                $current_case_total_claim += $claimline['amount'];
                            }
                        } else {
                            $active_cases_total_claim += $claimline['amount'];
                            $current_case_total_claim += $claimline['amount'];
                        }

                        if($claimline['claim_type'] == 1) {
                            $active_cases_main_claim += $claimline['amount'];
                            $current_case_main_claim += $claimline['amount'];
                            $mainclaimline = $claimline;
                        }
                    }
                }
                if($mainclaimline) {
                    foreach($payments as $payment) {
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$mainclaimline['cs_bookaccount_id']."' ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_cases_main_claim += $transaction['amount'];
                            $current_case_main_claim += $transaction['amount'];
                        }
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$mainclaimline['cs_bookaccount_creditor']."' ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_cases_main_claim += $transaction['amount'];
                            $current_case_main_claim += $transaction['amount'];
                        }
                    }
                }
                $active_case['main_claim'] = round($current_case_main_claim, 2);
                $active_case['total_claim'] = round($current_case_total_claim, 2);
                $processes_active_cases[] = $active_case;
            }
            //formText_MainClaimInActiveCasesInBothLevels_Output
            $v_count['value_7'] = $active_cases_main_claim;
            //formText_MainClaimInActiveCasesInBothLevels_Output
            $v_count['value_8'] = $active_cases_total_claim;
            
            if($info != '') {  
                $v_count['value_7_info'] = $processes_active_cases;
                $v_count['value_8_info'] = $processes_active_cases;
            }
        }

        if($info == '' || $info == 'value_9' || $info == 'value_10' || $info == 'value_11') { 
            $s_sql = "SELECT * FROM
            collecting_company_cases AS cc
            WHERE 1=1 ".$sql_reminder_level.$sql_reminder_level_active.$sql_active_with_closed.$sql_content_status;
            $o_query = $o_main->db->query($s_sql);
            $active_cases_in_reminder_level = $o_query ? $o_query->result_array() : array();
            //formText_NumberOfActiveCasesInReminderlevel_Output
            $v_count['value_9'] = count($active_cases_in_reminder_level);
            $active_cases_in_reminder_level_main_claim = 0;
            $active_cases_in_reminder_level_total_claim = 0;
            foreach($active_cases_in_reminder_level as $active_case_in_reminder_level){
                $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ?
                AND date <= '".$o_main->db->escape_str($s_period_end)."'
                ORDER BY created ASC";
                $o_query = $o_main->db->query($s_sql, array($active_case_in_reminder_level['id']));
                $payments = ($o_query ? $o_query->result_array() : array());

                $s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
                collecting_company_cases_claim_lines AS ccccl
                JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
                WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($active_case_in_reminder_level['id'])."' AND IFNULL(cccltb.not_include_in_claim, 0) = 0";
                $o_query = $o_main->db->query($s_sql);
                $claimlines = $o_query ? $o_query->result_array() : array();
                $mainclaimline = array();
                foreach($claimlines as $claimline){
                    if($claimline['date'] == '0000-00-00' OR strtotime($claimline['date']) <= strtotime($s_period_end) ){
                        if($claimline['claim_type'] == 15 || $claimline['claim_type'] == 16 || $claimline['claim_type'] == 18) {
                            if(!$claimline['payment_after_closed']){
                                $active_cases_in_reminder_level_main_claim += $claimline['amount'];
                                $active_cases_in_reminder_level_total_claim += $claimline['amount'];
                            }
                        } else {
                            $active_cases_in_reminder_level_total_claim += $claimline['amount'];
                        }

                        if($claimline['claim_type'] == 1) {
                            $active_cases_in_reminder_level_main_claim += $claimline['amount'];
                            $mainclaimline = $claimline;
                        }
                    }
                }

                if($mainclaimline) {
                    foreach($payments as $payment) {
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$mainclaimline['cs_bookaccount_id']."' ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_cases_in_reminder_level_main_claim += $transaction['amount'];
                        }
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$mainclaimline['cs_bookaccount_creditor']."' ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_cases_in_reminder_level_main_claim += $transaction['amount'];
                        }
                    }
                }
            }
            //formText_MainClaimInActiveCasesInReminderlevel_Output
            $v_count['value_10'] = $active_cases_in_reminder_level_main_claim;
            //formText_TotalClaimInActiveCasesInReminderlevel_Output
            $v_count['value_11'] = $active_cases_in_reminder_level_total_claim;
        }
        /*
        // $s_sql = "SELECT COUNT(cc.id) AS cnt, SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim
        // FROM collecting_company_cases AS cc WHERE (IFNULL(cc.case_closed_date, '0000-00-00') = '0000-00-00' OR
        // (cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."') AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."')
        // AND cc.collecting_case_created_date >= DATE_SUB(NOW(), INTERVAL 18 MONTH)";
        $s_sql = "SELECT COUNT(cc.id) AS cnt, SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim
        FROM collecting_company_cases AS cc WHERE 1=1 ".$sql_collecting_level." AND cc.collecting_case_created_date >= DATE_SUB('".$s_period_end."', INTERVAL 18 MONTH) AND cc.collecting_case_created_date <= '".$o_main->db->escape_str($s_period_end)."'".$sql_active_with_closed.$sql_content_status;
        $o_query = $o_main->db->query($s_sql);
        $v_row = $o_query ? $o_query->row_array() : array();
        //formText_NumberOfActiveCasesInReminderlevel_Output
        $v_count['value_12'] = $v_row['cnt'];
        //formText_MainClaimInActiveCasesInReminderlevel_Output
        $v_count['value_13'] = $v_row['original_main_claim'] - $v_row['collected_main_claim'];
        //formText_TotalClaimInActiveCasesInReminderlevel_Output
        $v_count['value_14'] = $v_row['current_total_claim'];

        $s_sql = "SELECT COUNT(cc.id) AS cnt, SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim
        FROM collecting_company_cases AS cc WHERE  1=1 ".$sql_collecting_level." AND cc.collecting_case_created_date < DATE_SUB('".$s_period_end."', INTERVAL 18 MONTH)".$sql_active_with_closed.$sql_content_status;
        $o_query = $o_main->db->query($s_sql);
        $v_row = $o_query ? $o_query->row_array() : array();
        //formText_NumberOfActiveCasesInReminderlevel_Output
        $v_count['value_15'] = $v_row['cnt'];
        //formText_MainClaimInActiveCasesInReminderlevel_Output
        $v_count['value_16'] = $v_row['original_main_claim'] - $v_row['collected_main_claim'];
        //formText_TotalClaimInActiveCasesInReminderlevel_Output
        $v_count['value_17'] = $v_row['current_total_claim'];

        $s_sql = "SELECT COUNT(cc.id) AS cnt FROM collecting_cases AS cc JOIN collecting_cases_payment_plan AS ccpp ON ccpp.collecting_case_id = cc.id WHERE ccpp.status IS NULL OR ccpp.status = 0";
        $o_query = $o_main->db->query($s_sql);
        $v_row = $o_query ? $o_query->row_array() : array();
        //formText_NumberOfActiveCasesInReminderlevel_Output
        $v_count['value_18'] = $v_row['cnt'];


        $s_sql = "SELECT SUM(cc.collected_main_claim) AS collected_main_claim FROM collecting_company_cases AS cc WHERE cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."' AND (cc.warning_case_created_date > DATE_SUB(cc.case_closed_date, INTERVAL 6 MONTH)".$sql_content_status;
        $o_query = $o_main->db->query($s_sql);
        $v_row = $o_query ? $o_query->row_array() : array();
        //formText_CollectedMainclaimWithCasetimeLessThan6Months_Output
        $v_count['value_19'] = $v_row['collected_main_claim'];
        */

        // $s_sql = "SELECT SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.collected_interest) AS collected_interest, SUM(cc.collected_legal_cost) AS collected_legal_cost, SUM(cc.collected_vat) AS collected_vat FROM collecting_company_cases AS cc WHERE cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."'".$sql_content_status;
        // $o_query = $o_main->db->query($s_sql);
        // $v_row = $o_query ? $o_query->row_array() : array();
        //
        // $s_sql = "SELECT cc.* FROM collecting_company_cases AS cc WHERE
        // cc.case_closed_date >= '".$o_main->db->escape_str($s_period_start)."'
        // AND cc.case_closed_date <= '".$o_main->db->escape_str($s_period_end)."'
        // ".$sql_content_status;
        // $o_query = $o_main->db->query($s_sql);
        // $closed_cases = $o_query ? $o_query->result_array() : array();
        // foreach($closed_cases as $closed_case) {
        // 	$s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
        // 	collecting_company_cases_claim_lines AS ccccl
        // 	JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
        // 	WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($closed_case['id'])."'";
        // 	$o_query = $o_main->db->query($s_sql);
        // 	$claimlines = $o_query ? $o_query->result_array() : array();
        // }

        $total_collected_mainclaim = 0;
        $total_collected_interest = 0;
        $total_collected_nonlegal_cost = 0;
        $total_collected_vat = 0;
        
        if($info == '' || $info == 'value_20' || $info == 'value_21' || $info == 'value_22' || $info == 'value_23') {     
            $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE date>='".$o_main->db->escape_str($s_period_start)."' AND date <= '".$o_main->db->escape_str($s_period_end)."' ORDER BY created ASC";
            $o_query = $o_main->db->query($s_sql);
            $payments = ($o_query ? $o_query->result_array() : array());
            $information_payments = array();
            foreach($payments as $payment) {
                $current_case_main_claim = 0;
                $current_case_interest = 0;
                $current_case_non_legal_cost = 0;
                $current_case_legal_cost = 0;

                $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '19' OR bookaccount_id = '20') ORDER BY id";
                $o_query = $o_main->db->query($s_sql);
                $transactions = ($o_query ? $o_query->result_array() : array());
                foreach($transactions as $transaction) {
                    $total_collected_mainclaim += $transaction['amount']*(-1);
                    $current_case_main_claim+=$transaction['amount']*(-1);
                }
                $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '7' OR bookaccount_id = '18') ORDER BY id";
                $o_query = $o_main->db->query($s_sql);
                $transactions = ($o_query ? $o_query->result_array() : array());
                foreach($transactions as $transaction) {
                    $total_collected_interest += $transaction['amount']*(-1);
                    $current_case_interest+=$transaction['amount']*(-1);
                }
                $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '5' OR bookaccount_id = '25' OR bookaccount_id = '6' OR bookaccount_id = '26' OR bookaccount_id = '3'  OR bookaccount_id = '23' OR bookaccount_id = '4' OR bookaccount_id = '24') ORDER BY id";
                $o_query = $o_main->db->query($s_sql);
                $transactions = ($o_query ? $o_query->result_array() : array());
                foreach($transactions as $transaction) {
                    $total_collected_nonlegal_cost += $transaction['amount']*(-1);
                    $current_case_non_legal_cost+=$transaction['amount']*(-1);
                }
                if($info != ""){
                    $payment['current_case_main_claim'] = $current_case_main_claim;
                    $payment['current_case_interest'] = $current_case_interest;
                    $payment['current_case_non_legal_cost'] = $current_case_non_legal_cost;
                    $information_payments[] = $payment;
                }
            }
            // foreach($claimlines as $claimline){
            // 	if($claimline['claim_type'] == 1) {
            // 		foreach($payments as $payment) {
            //
            // 			$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$claimline['cs_bookaccount_creditor']."' ORDER BY id";
            // 			$o_query = $o_main->db->query($s_sql);
            // 			$transactions = ($o_query ? $o_query->result_array() : array());
            // 			foreach($transactions as $transaction) {
            // 				$total_collected_mainclaim += $transaction['amount']*(-1);;
            // 			}
            // 		}
            // 	} else if($claimline['claim_type'] == 4 || $claimline['claim_type'] == 5 || $claimline['claim_type'] == 6 || $claimline['claim_type'] == 7) {
            // 		foreach($payments as $payment) {
            // 			$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$claimline['cs_bookaccount_id']."' ORDER BY id";
            // 			$o_query = $o_main->db->query($s_sql);
            // 			$transactions = ($o_query ? $o_query->result_array() : array());
            // 			foreach($transactions as $transaction) {
            // 				$total_collected_nonlegal_cost += $transaction['amount']*(-1);;
            // 			}
            // 			$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$claimline['cs_bookaccount_creditor']."' ORDER BY id";
            // 			$o_query = $o_main->db->query($s_sql);
            // 			$transactions = ($o_query ? $o_query->result_array() : array());
            // 			foreach($transactions as $transaction) {
            // 				$total_collected_nonlegal_cost += $transaction['amount']*(-1);;
            // 			}
            // 		}
            // 	} else if($claimline['claim_type'] == 8) {
            // 		foreach($payments as $payment) {
            //
            // 			$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$claimline['cs_bookaccount_creditor']."' ORDER BY id";
            // 			$o_query = $o_main->db->query($s_sql);
            // 			$transactions = ($o_query ? $o_query->result_array() : array());
            // 			foreach($transactions as $transaction) {
            // 				$total_collected_interest += $transaction['amount']*(-1);
            // 			}
            // 		}
            // 	}
            // }
            //formText_TotalCollectedMainclaim_Output
            $v_count['value_20'] = $total_collected_mainclaim;
            //formText_TotalCollectedInterests_Output
            $v_count['value_21'] = $total_collected_interest;
            //formText_TotalCollectedLegalCosts_Output
            $v_count['value_22'] = $total_collected_nonlegal_cost;
            //formText_TotalCollectedVat_Output
            $v_count['value_23'] = $total_collected_vat;
            if($info == "value_20"){
                $v_count['value_20_info'] = $information_payments;
            }
            if($info == "value_21"){
                $v_count['value_21_info'] = $information_payments;
            }
            if($info == "value_22"){
                $v_count['value_22_info'] = $information_payments;
            }
        }

        
        if($info == '' || $info == 'value_25') {     
            $s_sql = "SELECT COUNT(cc.id) AS cnt FROM collecting_company_cases
            AS cc LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
            1=1 ".$sql_new_cases_both_levels.$sql_content_status.$sql_company;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfActiveCasesInReminderlevelAtBeginningOfYear_Output
            $v_count['value_25'] = $v_row['cnt'];
            if($info != ''){                
                $s_sql = "SELECT cc.* FROM collecting_company_cases
                AS cc LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
                1=1 ".$sql_new_cases_both_levels.$sql_content_status.$sql_company;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_25_info'] = $results;
            }
        }

        if($info == '' || $info == 'value_26') { 
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases
            AS cc LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
            1=1 ".$sql_reminder_level.$sql_reminder_level_active.$sql_content_status.$sql_company;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfNewCompanyCasesInWarningLevelsSoFarThisYear_Output
            $v_count['value_26'] = $v_row['cnt'];
            if($info != ''){                
                $s_sql = "SELECT cc.* FROM collecting_company_cases
                AS cc LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
                1=1 ".$sql_reminder_level.$sql_reminder_level_active.$sql_content_status.$sql_company;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_26_info'] = $results;
            }
        }
        if($info == '' || $info == 'value_27') { 
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases
            AS cc
            LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
            1=1 ".$sql_new_cases_both_levels.$sql_content_status.$sql_person;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfNewPersonCasesInBothLevelsSoFarThisYear_Output
            $v_count['value_27'] = $v_row['cnt'];
            if($info != ''){                
                $s_sql = "SELECT cc.* FROM collecting_company_cases
                AS cc LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
                1=1 ".$sql_new_cases_both_levels.$sql_content_status.$sql_person;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_27_info'] = $results;
            }
        }
        if($info == '' || $info == 'value_28') { 
            $s_sql = "SELECT COUNT(*) AS cnt FROM collecting_company_cases
            AS cc
            LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
            1=1 ".$sql_reminder_level.$sql_reminder_level_active.$sql_content_status.$sql_person;
            $o_query = $o_main->db->query($s_sql);
            $v_row = $o_query ? $o_query->row_array() : array();
            //formText_NumberOfNewPersonCasesInWarningLevelsSoFarThisYear_Output
            $v_count['value_28'] = $v_row['cnt'];
            if($info != ''){                
                $s_sql = "SELECT cc.* FROM collecting_company_cases
                AS cc LEFT JOIN customer c ON c.id = cc.debitor_id WHERE
                1=1 ".$sql_reminder_level.$sql_reminder_level_active.$sql_content_status.$sql_person;
                $o_query = $o_main->db->query($s_sql);
                $results = $o_query ? $o_query->result_array() : array();
                $v_count['value_28_info'] = $results;
            }
        }


        if($info == '' || $info == 'value_29' || $info == 'value_30' || $info == 'value_31' || $info == 'value_31_1' || $info == 'value_31_2' || $info == 'value_31_3' ) { 
            $s_sql = "SELECT cc.* FROM collecting_company_cases AS cc
            LEFT JOIN customer c ON c.id = cc.debitor_id
            WHERE 1=1 ".$sql_active_with_closed.$sql_content_status.$sql_company;
            $o_query = $o_main->db->query($s_sql);
            $active_company_cases = $o_query ? $o_query->result_array() : array();
            //formText_NumberOfActiveCompanyCases_Output
            $v_count['value_29'] = count($active_company_cases);
            if($info != ''){
                $v_count['value_29_info'] = $active_company_cases;
            }
            $active_company_cases_main_claim = 0;
            $active_company_cases_total_claim = 0;
            $active_company_cases_interest = 0;
            $active_company_cases_non_legal_cost = 0;
            $active_company_cases_legal_cost = 0;
            $active_company_cases_processed = array();
            $total_type_amounts = array();
            foreach($active_company_cases as $active_case_in_reminder_level) {
                $current_case_main_claim=0;
                $current_case_total_claim=0;
                $current_case_interest=0;
                $current_case_non_legal_cost=0;
                $current_case_legal_cost=0;

                $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ?
                AND date <= '".$o_main->db->escape_str($s_period_end)."'
                ORDER BY created ASC";
                $o_query = $o_main->db->query($s_sql, array($active_case_in_reminder_level['id']));
                $payments = ($o_query ? $o_query->result_array() : array());

                $s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
                collecting_company_cases_claim_lines AS ccccl
                JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
                WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($active_case_in_reminder_level['id'])."' AND IFNULL(cccltb.not_include_in_claim, 0) = 0";
                $o_query = $o_main->db->query($s_sql);
                $claimlines = $o_query ? $o_query->result_array() : array();
                $interestclaimline = array();
                $mainclaimline = array();
                $nonlegal_claimline_types = array();
             
                foreach($claimlines as $claimline){
                    if($claimline['date'] == '0000-00-00' OR strtotime($claimline['date']) <= strtotime($s_period_end) ){
                        if($claimline['claim_type'] == 15 || $claimline['claim_type'] == 16 || $claimline['claim_type'] == 18) {
                            if(!$claimline['payment_after_closed']){
                                $active_company_cases_main_claim += $claimline['amount'];
                                $active_company_cases_total_claim += $claimline['amount'];
                                $current_case_main_claim += $claimline['amount'];
                                $current_case_total_claim += $claimline['amount'];
                                $total_type_amounts[$claimline['claim_type']] += $claimline['amount'];
                            }
                        } else {
                            $active_company_cases_total_claim += $claimline['amount'];
                            $current_case_total_claim += $claimline['amount'];
                            $total_type_amounts[$claimline['claim_type']] += $claimline['amount'];
                        }

                        if($claimline['claim_type'] == 1) {
                            $active_company_cases_main_claim += $claimline['amount'];
                            $mainclaimline = $claimline;
                            $current_case_main_claim += $claimline['amount'];
                        }
                        if($claimline['claim_type'] == 8) {
                            $active_company_cases_interest += $claimline['amount'];
                            $current_case_interest += $claimline['amount'];
                            $interestclaimline = $claimline;
                        }
                        if($claimline['claim_type'] == 4 || $claimline['claim_type'] == 5 || $claimline['claim_type'] == 6  || $claimline['claim_type'] == 7){
                            $active_company_cases_non_legal_cost += $claimline['amount'];
                            $current_case_non_legal_cost += $claimline['amount'];
                            if(!in_array($claimline['claim_type'], $nonlegal_claimline_types)) {
                                $nonlegal_claimline_types[] = $claimline['claim_type'];
                            }
                        }
                        if($claimline['claim_type'] == 9 || $claimline['claim_type'] == 10){
                            $active_company_cases_legal_cost += $claimline['amount'];
                            $current_case_legal_cost += $claimline['amount'];
                        }
                    }
                }
                if($mainclaimline) {
                    foreach($payments as $payment) {
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '".$mainclaimline['cs_bookaccount_id']."' OR bookaccount_id = '".$mainclaimline['cs_bookaccount_creditor']."') ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_company_cases_main_claim += $transaction['amount'];
                            $current_case_main_claim += $transaction['amount'];
                        }
                    }
                }
                if($interestclaimline){
                    foreach($payments as $payment) {
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '".$interestclaimline['cs_bookaccount_id']."' OR bookaccount_id = '".$interestclaimline['cs_bookaccount_creditor']."') ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_company_cases_interest += $transaction['amount'];
                            $current_case_interest += $transaction['amount'];
                        }
                    }
                }
                if(count($nonlegal_claimline_types) > 0){
                    $s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
                    collecting_company_cases_claim_lines AS ccccl
                    JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
                    WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($active_case_in_reminder_level['id'])."' AND IFNULL(cccltb.not_include_in_claim, 0) = 0 AND ccccl.claim_type IN (".implode(",", $nonlegal_claimline_types).")
                    GROUP BY ccccl.claim_type";
                    $o_query = $o_main->db->query($s_sql);
                    $claimlines = $o_query ? $o_query->result_array() : array();
                    foreach($claimlines as $claimline){
                        foreach($payments as $payment) {
                            $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '".$claimline['cs_bookaccount_id']."' OR bookaccount_id = '".$claimline['cs_bookaccount_creditor']."') ORDER BY id";
                            $o_query = $o_main->db->query($s_sql);
                            $transactions = ($o_query ? $o_query->result_array() : array());
                            foreach($transactions as $transaction) {
                                $active_company_cases_non_legal_cost += $transaction['amount'];
                                $current_case_non_legal_cost += $transaction['amount'];
                            }
                        }
                    }
                }
                $active_case_in_reminder_level['current_case_main_claim'] = $current_case_main_claim;
                $active_case_in_reminder_level['current_case_total_claim'] = $current_case_total_claim;
                $active_case_in_reminder_level['current_case_interest'] = $current_case_interest;
                $active_case_in_reminder_level['current_case_non_legal_cost'] = $current_case_non_legal_cost;
                $active_case_in_reminder_level['current_case_legal_cost'] = $current_case_legal_cost;

                $active_company_cases_processed[] = $active_case_in_reminder_level;
            }
            //formText_MainClaimInActiveCompanyCases_Output
            $v_count['value_30'] = $active_company_cases_main_claim;
            //formText_TotalClaimInActiveCompanyCases_Output
            $v_count['value_31'] = $active_company_cases_total_claim;
            $v_count['value_31_array'] = $total_type_amounts;
            //formText_InterestInActiveCompanyCases_Output
            $v_count['value_31_1'] = $active_company_cases_interest;
            $v_count['value_31_2'] = $active_company_cases_non_legal_cost;
            $v_count['value_31_3'] = $active_company_cases_legal_cost;
            if($info != '') {
                $v_count['value_30_info'] = $active_company_cases_processed;
                $v_count['value_31_info'] = $active_company_cases_processed;
                $v_count['value_31_1_info'] = $active_company_cases_processed;
                $v_count['value_31_2_info'] = $active_company_cases_processed;
            }
        }
        if($info == '' || $info == 'value_32' || $info == 'value_33' || $info == 'value_34' || $info == 'value_34_1' || $info == 'value_34_2' || $info == 'value_34_3' || $info == 'value_35'
        || $info == 'value_36' || $info == 'value_37' || $info == 'value_38' || $info == 'value_39' || $info == 'value_40') {
            $s_sql = "SELECT cc.*, IFNULL(cc.collecting_case_created_date, '0000-00-00') as collecting_case_created_date, IFNULL(cc.warning_case_created_date, '0000-00-00') as warning_case_created_date FROM collecting_company_cases AS cc
            LEFT JOIN customer c ON c.id = cc.debitor_id
            WHERE 1=1 ".$sql_active_with_closed.$sql_content_status.$sql_person;
            $o_query = $o_main->db->query($s_sql);
            $active_person_cases = $o_query ? $o_query->result_array() : array();
            //formText_NumberOfActivePersonCases_Output
            $v_count['value_32'] = count($active_person_cases);
            if($info == 'value_32'){
                $v_count['value_32_info'] = $active_person_cases;
            }
            $active_person_cases_main_claim = 0;
            $active_person_cases_total_claim = 0;
            $active_person_cases_interest = 0;
            $active_person_cases_non_legal_cost = 0;
            $active_person_cases_legal_cost = 0;

            $number_level_1 = 0;
            $number_level_2 = 0;
            $number_level_3 = 0;
            $number_level_4 = 0;
            $number_level_5 = 0;
            $number_level_6 = 0;
            $number_level_7 = 0;
            $number_level_8 = 0;
            $number_level_9 = 0;
            $number_level_10 = 0;
            $number_level_11 = 0;

            $main_claim_level_1 = 0;
            $main_claim_level_2 = 0;
            $main_claim_level_3 = 0;
            $main_claim_level_4 = 0;
            $main_claim_level_5 = 0;
            $main_claim_level_6 = 0;
            $main_claim_level_7 = 0;
            $main_claim_level_8 = 0;
            $main_claim_level_9 = 0;
            $main_claim_level_10 = 0;
            $main_claim_level_11 = 0;

            $main_claim_year_level_1 = 0;
            $main_claim_year_level_2 = 0;
            $main_claim_year_level_3 = 0;
            $main_claim_year_level_4 = 0;
            $main_claim_year_level_5 = 0;
            $main_claim_year_level_6 = 0;

            $number_year_level_1 = 0;
            $number_year_level_2 = 0;
            $number_year_level_3 = 0;
            $number_year_level_4 = 0;
            $number_year_level_5 = 0;
            $number_year_level_6 = 0;
            $active_person_cases_processed = array();
            foreach($active_person_cases as $active_case_in_reminder_level) {                
                $current_case_main_claim=0;
                $current_case_total_claim=0;
                $current_case_interest=0;
                $current_case_non_legal_cost=0;
                $current_case_legal_cost=0;

                $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ?
                AND date <= '".$o_main->db->escape_str($s_period_end)."'
                ORDER BY created ASC";
                $o_query = $o_main->db->query($s_sql, array($active_case_in_reminder_level['id']));
                $payments = ($o_query ? $o_query->result_array() : array());

                $s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
                collecting_company_cases_claim_lines AS ccccl
                JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
                WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($active_case_in_reminder_level['id'])."' AND IFNULL(cccltb.not_include_in_claim, 0) = 0";
                $o_query = $o_main->db->query($s_sql);
                $claimlines = $o_query ? $o_query->result_array() : array();
                $mainclaimline = array();
                $interestclaimline = array();
                $case_main_claim_amount = 0;
                $nonlegal_claimline_types = array();
                foreach($claimlines as $claimline){
                    if($claimline['date'] == '0000-00-00' OR strtotime($claimline['date']) <= strtotime($s_period_end) ){
                        if($claimline['claim_type'] == 15 || $claimline['claim_type'] == 16 || $claimline['claim_type'] == 18) {
                            if(!$claimline['payment_after_closed']){
                                $active_person_cases_main_claim += $claimline['amount'];
                                $active_person_cases_total_claim += $claimline['amount'];
                                $case_main_claim_amount += $claimline['amount'];

                                $current_case_main_claim += $claimline['amount'];
                                $current_case_total_claim += $claimline['amount'];
                            }
                        } else {
                            $active_person_cases_total_claim += $claimline['amount'];
                            $current_case_total_claim += $claimline['amount'];
                        }

                        if($claimline['claim_type'] == 1) {
                            $case_main_claim_amount += $claimline['amount'];
                            $active_person_cases_main_claim += $claimline['amount'];
                            $mainclaimline = $claimline; 
                            
                            $current_case_main_claim += $claimline['amount'];
                        }
                        if($claimline['claim_type'] == 8) {
                            $active_person_cases_interest += $claimline['amount'];
                            $interestclaimline = $claimline;
                            $current_case_interest += $claimline['amount'];
                        }
                        if($claimline['claim_type'] == 4 || $claimline['claim_type'] == 5 || $claimline['claim_type'] == 6  || $claimline['claim_type'] == 7){
                            $active_person_cases_non_legal_cost += $claimline['amount'];
                            $current_case_non_legal_cost += $claimline['amount'];
                            if(!in_array($claimline['claim_type'], $nonlegal_claimline_types)) {
                                $nonlegal_claimline_types[] = $claimline['claim_type'];
                            }
                        }
                        if($claimline['claim_type'] == 9 || $claimline['claim_type'] == 10) {
                            $active_person_cases_legal_cost += $claimline['amount'];
                            $current_case_legal_cost += $claimline['amount'];
                        }
                    }
                }
                if($mainclaimline) {
                    foreach($payments as $payment) {
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '".$mainclaimline['cs_bookaccount_id']."' OR bookaccount_id = '".$mainclaimline['cs_bookaccount_creditor']."') ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_person_cases_main_claim += $transaction['amount'];
                            $case_main_claim_amount += $transaction['amount'];

                            $current_case_main_claim += $transaction['amount'];
                        }
                    }
                }
                if($interestclaimline){
                    foreach($payments as $payment) {
                        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '".$interestclaimline['cs_bookaccount_id']."' OR bookaccount_id = '".$interestclaimline['cs_bookaccount_creditor']."') ORDER BY id";
                        $o_query = $o_main->db->query($s_sql);
                        $transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($transactions as $transaction) {
                            $active_person_cases_interest += $transaction['amount'];
                            $current_case_interest += $transaction['amount'];
                        }
                    }
                }

                if(count($nonlegal_claimline_types) > 0){
                    $s_sql = "SELECT ccccl.*, cccltb.cs_bookaccount_id, cccltb.cs_bookaccount_creditor, IFNULL(ccccl.date, '0000-00-00') as date FROM
                    collecting_company_cases_claim_lines AS ccccl
                    JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.id = ccccl.claim_type
                    WHERE ccccl.collecting_company_case_id = '".$o_main->db->escape_str($active_case_in_reminder_level['id'])."'  AND IFNULL(cccltb.not_include_in_claim, 0) = 0 AND ccccl.claim_type IN (".implode(",", $nonlegal_claimline_types).")
                    GROUP BY ccccl.claim_type";
                    $o_query = $o_main->db->query($s_sql);
                    $claimlines = $o_query ? $o_query->result_array() : array();
                    foreach($claimlines as $claimline){
                        foreach($payments as $payment) {
                            $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND (bookaccount_id = '".$claimline['cs_bookaccount_id']."' OR bookaccount_id = '".$claimline['cs_bookaccount_creditor']."') ORDER BY id";
                            $o_query = $o_main->db->query($s_sql);
                            $transactions = ($o_query ? $o_query->result_array() : array());
                            foreach($transactions as $transaction) {
                                $active_person_cases_non_legal_cost += $transaction['amount'];
                                $current_case_non_legal_cost += $transaction['amount'];
                            }
                        }
                    }
                }

                if($case_main_claim_amount<=500){
                    $main_claim_level_1 += $case_main_claim_amount;
                    $number_level_1++;
                } else if($case_main_claim_amount >= 501 && $case_main_claim_amount<=1000){
                    $main_claim_level_2 += $case_main_claim_amount;
                    $number_level_2++;
                } else if($case_main_claim_amount >= 1001 && $case_main_claim_amount<=2500){
                    $main_claim_level_3 += $case_main_claim_amount;
                    $number_level_3++;
                } else if($case_main_claim_amount >= 2501 && $case_main_claim_amount<=10000){
                    $main_claim_level_4 += $case_main_claim_amount;
                    $number_level_4++;
                } else if($case_main_claim_amount >= 10001 && $case_main_claim_amount<=50000){
                    $main_claim_level_5 += $case_main_claim_amount;
                    $number_level_5++;
                } else if($case_main_claim_amount >= 50001 && $case_main_claim_amount<=250000){
                    $main_claim_level_6 += $case_main_claim_amount;
                    $number_level_6++;
                } else if($case_main_claim_amount >= 250001 && $case_main_claim_amount<=500000){
                    $main_claim_level_7 += $case_main_claim_amount;
                    $number_level_7++;
                } else if($case_main_claim_amount >= 500001 && $case_main_claim_amount<=1000000){
                    $main_claim_level_8 += $case_main_claim_amount;
                    $number_level_8++;
                } else if($case_main_claim_amount >= 1000001 && $case_main_claim_amount<=3000000){
                    $main_claim_level_9 += $case_main_claim_amount;
                    $number_level_9++;
                } else if($case_main_claim_amount >= 3000001 && $case_main_claim_amount<=5000000){
                    $main_claim_level_10 += $case_main_claim_amount;
                    $number_level_10++;
                } else if($case_main_claim_amount > 5000000){
                    $main_claim_level_11 += $case_main_claim_amount;
                    $number_level_11++;
                }
                if($active_case_in_reminder_level['collecting_case_created_date'] !="0000-00-00") {
                    $date1 = new DateTime($active_case_in_reminder_level['collecting_case_created_date']);
                } else {
                    $date1 = new DateTime($active_case_in_reminder_level['warning_case_created_date']);
                }
                $date2 = new DateTime($s_period_end);
                $interval = $date1->diff($date2);
                $yearDifference = $interval->y;

                if($yearDifference == 0){
                    $main_claim_year_level_1 += $case_main_claim_amount;
                    $number_year_level_1++;
                } else if($yearDifference == 1){
                    $main_claim_year_level_2 += $case_main_claim_amount;
                    $number_year_level_2++;
                } else if($yearDifference == 2){
                    $main_claim_year_level_3 += $case_main_claim_amount;
                    $number_year_level_3++;
                } else if($yearDifference >= 3 && $yearDifference <5) {
                    $main_claim_year_level_4 += $case_main_claim_amount;
                    $number_year_level_4++;
                } else if($yearDifference >= 5 && $yearDifference < 10) {
                    $main_claim_year_level_5 += $case_main_claim_amount;
                    $number_year_level_5++;
                }else if($yearDifference >= 10){
                    $main_claim_year_level_6 += $case_main_claim_amount;
                    $number_year_level_6++;
                }
                $active_case_in_reminder_level['current_case_main_claim'] = $current_case_main_claim;
                $active_case_in_reminder_level['current_case_total_claim'] = $current_case_total_claim;
                $active_case_in_reminder_level['current_case_interest'] = $current_case_interest;
                $active_case_in_reminder_level['current_case_non_legal_cost'] = $current_case_non_legal_cost;
                $active_case_in_reminder_level['current_case_legal_cost'] = $current_case_legal_cost;

                $active_company_cases_processed[] = $active_case_in_reminder_level;
            }
            //formText_MainClaimInActivePersonCases_Output
            $v_count['value_33'] = $active_person_cases_main_claim;
            //formText_TotalClaimInActivePersonCases_Output
            $v_count['value_34'] = $active_person_cases_total_claim;
            //formText_InterestInActivePersonCases_Output
            $v_count['value_34_1'] = $active_person_cases_interest;
            $v_count['value_34_2'] = $active_person_cases_non_legal_cost;
            $v_count['value_34_3'] = $active_person_cases_legal_cost;

            if($info != ''){
                $v_count['value_30_info'] = $active_person_cases_processed;
                $v_count['value_31_info'] = $active_person_cases_processed;
                $v_count['value_31_1_info'] = $active_person_cases_processed;
                $v_count['value_31_2_info'] = $active_person_cases_processed;
            }

            //formText_PersonCasesMainClaimFor0To500_Output
            $v_count['value_35'] = $main_claim_level_1;
            //formText_PersonCasesMainClaimFor500To1000_Output
            $v_count['value_36'] = $main_claim_level_2;
            //formText_PersonCasesMainClaimFor1000To2500_Output
            $v_count['value_37'] = $main_claim_level_3;
            //formText_PersonCasesMainClaimFor2500To10000_Output
            $v_count['value_38'] = $main_claim_level_4;
            //formText_PersonCasesMainClaimFor10000To50000_Output
            $v_count['value_39'] = $main_claim_level_5;
            //formText_PersonCasesMainClaimFor50000To250000_Output
            $v_count['value_40'] = $main_claim_level_6;
            //formText_PersonCasesMainClaimFor250000To500000_Output
            $v_count['value_41'] = $main_claim_level_7;
            //formText_PersonCasesMainClaimFor500000To1000000_Output
            $v_count['value_42'] = $main_claim_level_8;
            //formText_PersonCasesMainClaimFor1000000To3000000_Output
            $v_count['value_43'] = $main_claim_level_9;
            //formText_PersonCasesMainClaimFor3000000To5000000_Output
            $v_count['value_44'] = $main_claim_level_10;
            //formText_PersonCasesMainClaimOver5000000_Output
            $v_count['value_45'] = $main_claim_level_11;

            $v_count['value_46'] = $main_claim_year_level_1;
            $v_count['value_47'] = $main_claim_year_level_2;
            $v_count['value_48'] = $main_claim_year_level_3;
            $v_count['value_49'] = $main_claim_year_level_4;
            $v_count['value_50'] = $main_claim_year_level_5;
            $v_count['value_51'] = $main_claim_year_level_6;

            $v_count['value_52'] = $number_level_1;
            $v_count['value_53'] = $number_level_2;
            $v_count['value_54'] = $number_level_3;
            $v_count['value_55'] = $number_level_4;
            $v_count['value_56'] = $number_level_5;
            $v_count['value_57'] = $number_level_6;
            $v_count['value_58'] = $number_level_7;
            $v_count['value_59'] = $number_level_8;
            $v_count['value_60'] = $number_level_9;
            $v_count['value_61'] = $number_level_10;
            $v_count['value_62'] = $number_level_11;

            $v_count['value_63'] = $number_year_level_1;
            $v_count['value_64'] = $number_year_level_2;
            $v_count['value_65'] = $number_year_level_3;
            $v_count['value_66'] = $number_year_level_4;
            $v_count['value_67'] = $number_year_level_5;
            $v_count['value_68'] = $number_year_level_6;
        }
        return $v_count;
    }
}

?>