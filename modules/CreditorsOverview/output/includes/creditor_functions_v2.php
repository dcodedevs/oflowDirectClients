<?php

function get_collecting_company_case_count2($o_main, $cid, $filter, $filters){
    return get_collecting_company_case_list($o_main, $cid, $filter, $filters, 0, 0, TRUE);
}
function get_collecting_company_case_count($o_main, $cid, $filter, $filters){
    $filters = array();
    return get_collecting_company_case_list($o_main, $cid, $filter, $filters, 0, 0, TRUE);
}
function get_collecting_company_case_list($o_main, $cid, $filter, $filters, $page=1, $perPage=100, $b_countonly = FALSE) {
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($cid));
    $creditor = ($o_query ? $o_query->row_array() : array());

	$sql_join = "";
    $sql_join_personal = "";
    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_having = "";
    $sql_where = " AND p.creditor_id = ".$o_main->db->escape($cid);
	$sql_join = " LEFT JOIN collecting_company_case_paused obj ON obj.collecting_company_case_id = p.id AND IFNULL(obj.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";

    $status_sql = "";
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    if($page == -1) {
        $pager = "";
    }

   $list_filter = "";
   $sql_order_direction = " ASC";
   foreach($filters as $filterName=>$filterValue) {
       switch($filterName){
           case "order_direction":
               switch($filterValue) {
                   case "0";
                       $sql_order_direction = " ASC";
                   break;
                   case "1";
                       $sql_order_direction = " DESC";
                   break;
               }
           break;
       }
   }

   $sql_order = " ORDER BY p.created ";
   $sql_order_direction = " DESC ";

   if($filter == "warning") {
	   $sql_where .= " AND IFNULL(p.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND IFNULL(p.collecting_case_created_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.case_closed_date, '0000-00-00') = '0000-00-00'";
   } else if($filter == "warning_closed") {
	   $sql_where .= " AND IFNULL(p.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND IFNULL(p.collecting_case_created_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.case_closed_date, '0000-00-00') <> '0000-00-00'";
   } else if($filter == "collecting"){
	   $sql_where .= " AND (IFNULL(p.collecting_case_created_date, '0000-00-00') <> '0000-00-00' OR IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') <> '0000-00-00') AND IFNULL(p.case_closed_date, '0000-00-00') = '0000-00-00'";
   } else if($filter == "collecting_closed"){
	   $sql_where .= " AND (IFNULL(p.collecting_case_created_date, '0000-00-00') <> '0000-00-00' OR IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') <> '0000-00-00') AND IFNULL(p.case_closed_date, '0000-00-00') <> '0000-00-00'";
   } else if($filter == "all_not_started"){
	   $sql_where .= " AND (p.collecting_cases_process_step_id = 0 OR p.collecting_cases_process_step_id is null)";
	   $sql_order = " ORDER BY p.created DESC ";
   } else if($filter == "all_active"){
	   $sql_where .= " AND IFNULL(p.case_closed_date, '0000-00-00') = '0000-00-00' AND p.collecting_cases_process_step_id > 0";
	   $sql_order = " ORDER BY p.created DESC ";
   } else if($filter == "all_closed"){
	   $sql_where .= " AND IFNULL(p.case_closed_date, '0000-00-00') <> '0000-00-00' AND p.collecting_cases_process_step_id > 0";
	   $sql_order = " ORDER BY p.case_closed_date DESC ";
   }

   foreach($filters as $filterName=>$filterValue) {
	   switch($filterName){
		   case "search_filter":
			   if($filterValue != ""){
				   $sql_join .= "";
				   $sql_where .= " AND (c2.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.id LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR ct.invoice_nr = '".$o_main->db->escape_str($filterValue)."')";
			   }
		   break;
		   case "sublist_filter":
			    if($filterValue == "canSendNow") {
				   $sql_where .= " AND ((p.id is null) OR (p.id is not null AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) <= NOW() AND (p.onhold_by_creditor is null OR  p.onhold_by_creditor = 0) AND (obj.id is null)))";
			    } else if($filterValue == "dueDateNotExpired"){
				   $sql_where .= " AND p.id is not null AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) > NOW()  AND (p.onhold_by_creditor is null OR  p.onhold_by_creditor = 0) AND (obj.id is null)";
			    } else if($filterValue == "stoppedWithObjection") {
				   $sql_where .= " AND (obj.id is NOT NULL) AND  (obj.closed_date = '0000-00-00' OR obj.closed_date is null)";
			    } else if($filterValue == "notStarted") {
				   $sql_where .= " AND (p.collecting_cases_process_step_id = 0 OR p.collecting_cases_process_step_id is null)";
			    }
		   break;
		   case "order_direction":
			   switch(intval($filterValue)) {
				   case "0";
					   $sql_order_direction = " DESC";
				   break;
				   case "1";
					   $sql_order_direction = " ASC";
				   break;
			   }
		   break;
		   case "order_field":
			   switch($filterValue) {
				   case "customer_name";
					   $sql_order = " ORDER BY debitorName ".$sql_order_direction;
				   break;
				   case "created";
					   $sql_order = " ORDER BY p.created ".$sql_order_direction;
				   break;
				   case "completed";
					   $sql_order = " ORDER BY p.case_closed_date ".$sql_order_direction;
				   break;
			   }
		   break;
	   }
   }


  // var_dump(date("H:i:s"));
  $sql = "SELECT ".($b_countonly?"p.id":"p.*, cred.companyname as creditorName, c2.name as debitorName, c2.publicRegisterId, c2.creditor_customer_id,
	  DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		  IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType,
		  step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId, GROUP_CONCAT(ct.invoice_nr) as invoice_nrs")."
		   FROM collecting_company_cases p
		   JOIN creditor cred ON cred.id = p.creditor_id
           LEFT JOIN creditor_transactions ct ON ct.collecting_company_case_id = p.id
		   LEFT JOIN customer c2 ON c2.id = p.debitor_id
		   LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
		   LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
		  ".$sql_join."
		  WHERE p.content_status < 2 ".$sql_where;
      $sql .= " GROUP BY p.id";
    
    if($page == 0 && $perPage == 0){
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query){
            $rowCount = $o_query->num_rows();
        }
        return $rowCount;
    } else {
        $sql .= $sql_order.$pager;
        $f_check_sql = $sql;
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $list = $o_query->result_array();
        }
        // var_dump(date("H:i:s"));
        return $list;
    }
}
function get_transaction_count_filtered($o_main, $cid, $filter, $filters){
    return get_transaction_list($o_main, $cid, $filter, $filters, 0, 0, FALSE, TRUE);
}
function get_transaction_count2($o_main, $cid, $filter, $filters){
    $filters['search_filter'] = "";
    return get_transaction_list($o_main, $cid, $filter, $filters, 0, 0, FALSE, TRUE);
}
function get_transaction_count($o_main, $cid, $filter, $filters){
    $filters = array();
    return get_transaction_list($o_main, $cid, $filter, $filters, 0, 0, FALSE, TRUE);
}
function get_transaction_list($o_main, $cid, $filter, $filters, $page=1, $perPage=100, $show_all = false, $b_countonly = FALSE) {
    global $variables;
    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($cid));
    $creditor = ($o_query ? $o_query->row_array() : array());
    
    $s_sql = "SELECT * FROM collecting_system_settings";
    $o_query = $o_main->db->query($s_sql);
    $collecting_system_settings = ($o_query ? $o_query->row_array() : array());
    $creditor_sql = "";
    $sql_join = "";
    $sql_join_personal = "";
    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_having = "";
    $sql_where = " AND ct.creditor_id = ".$o_main->db->escape($cid);
    $status_sql = "";
    if($page == 0 && $perPage == 0) {
        $pager = "";
    }
    if($page == -1) {
        $pager = "";
    }
    if($filter == "reminderLevel"){
        $sql_where_open_status = " AND ct.tab_status > 0 AND ct.open = 1 AND (ct.collecting_company_case_id IS NULL or ct.collecting_company_case_id = 0)";
		if($creditor['reminder_only_from_invoice_nr'] > 0) {
			$creditor_sql = " AND ct.invoice_nr >= '".$o_main->db->escape_str($creditor['reminder_only_from_invoice_nr'])."'";
		}
        $list_filter = "";
        $sql_order_direction = " ASC";
        foreach($filters as $filterName=>$filterValue) {
           switch($filterName){
               case "order_direction":
                   switch($filterValue) {
                       case "0";
                           $sql_order_direction = " ASC";
                       break;
                       case "1";
                           $sql_order_direction = " DESC";
                       break;
                   }
               break;
           }
        }
        $sql_order = " ORDER BY ct.invoice_nr ".$sql_order_direction;
        $list_filter = $filters['list_filter'];
        $sublist_filter = $filters['sublist_filter'];
        $active_search_filter = false;
        foreach($filters as $filterName=>$filterValue) {
           switch($filterName){
               case "search_filter":
                   if($filterValue != ""){
                        $active_search_filter = true;
                        $sql_join .= "";
                        if($list_filter == "search_by_invoice_tab"){
                            $sql_where .= " AND ct.invoice_nr = '".$o_main->db->escape_str($filterValue)."'";
                            
                        } else {                            
                            $sql_where .= " AND (ct.customer_name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR ct.invoice_nr LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
                        }
                    }
               break;
               case "order_field":
                   switch($filterValue) {
                       case "invoice_no";
                           $sql_order = " ORDER BY ct.invoice_nr ".$sql_order_direction;
                       break;
                       case "debitor";
                           $sql_order = " ORDER BY debitorName ".$sql_order_direction;
                       break;
                       default:
                           $sql_order = " ORDER BY ct.invoice_nr ".$sql_order_direction;
                       break;
                   }
               break;               
           }
        }
        if($list_filter == "canSendReminderNow") {
            if($sublist_filter == "automatic_move"){
                $sql_where .= " AND ct.tab_status = 2";
            } else if($sublist_filter == "manual_move") {
                $sql_where .= " AND ct.tab_status = 1";
            } else if($sublist_filter == "missing_address"){
                $sql_where .= " AND ct.tab_status = 3";
            } else if($sublist_filter == "small_amount"){
                $sql_where .= " AND ct.tab_status = 10";
            } else {
                $sql_where .= " AND (ct.tab_status = 1 OR ct.tab_status = 2 OR ct.tab_status = 3)";
            }
        } else if($list_filter=="dueDateNotExpired") {
            if($sublist_filter == "reminder_sent") {
                $sql_where .= " AND ct.tab_status = 4";
            } else if($sublist_filter == "reminder_not_sent") {
                $sql_where .= " AND ct.tab_status = 13";
            } else {
                $sql_where .= " AND (ct.tab_status = 4 OR ct.tab_status = 13)";
            }
        } else if($list_filter=="doNotSend") {
            $sql_where .= " AND ct.tab_status = 5";
        } else if($list_filter=="stoppedWithObjection") {
            $sql_where .= " AND ct.tab_status = 6";
        } else if($list_filter=="notPayedConsiderCollectingProcess") {
            if($sublist_filter == "automatic_move"){
                $sql_where .= " AND ct.tab_status = 8";
            } else if($sublist_filter == "manual_move") {
                $sql_where .= " AND ct.tab_status = 7";
            } else if($sublist_filter == "missing_address"){
                $sql_where .= " AND ct.tab_status = 9";
            } else if($sublist_filter == "small_amount"){
                $sql_where .= " AND ct.tab_status = 11";
            } else {
                $sql_where .= " AND (ct.tab_status = 7 OR ct.tab_status = 8 OR ct.tab_status = 9)";
            }
        } else if($list_filter =="allTransactionsWithoutCases") {
            $sql_where .= " AND IFNULL(ct.collectingcase_id, 0) = 0 AND IFNULL(ct.collecting_company_case_id, 0) = 0";
        }else if($list_filter =="transactions_with_fees_only") {
            $sql_where .= " AND ct.tab_status = 12";
            $sql_order = " ORDER BY ct.current_due_date ASC";
            if($sublist_filter == "restclaim_older") {
                // $sql_join .= " LEFT JOIN collecting_cases_claim_letter cccl ON cccl.case_id = ct.collectingcase_id AND cccl.rest_note = 1";
                $sql_where .= " AND IFNULL(ct.current_due_date, '0000-00-00') < DATE_SUB(CURDATE(), INTERVAL 16 DAY) ";
            } else if($sublist_filter == "resclaim_younger") {
                // $sql_join .= " LEFT JOIN collecting_cases_claim_letter cccl ON cccl.case_id = ct.collectingcase_id AND cccl.rest_note = 1";
                
                $sql_where .= " AND IFNULL(ct.current_due_date, '0000-00-00') >= DATE_SUB(CURDATE(), INTERVAL 16 DAY) ";
            } else if($sublist_filter == "resclaim_need_date_fix"){
                $sql_join .= " JOIN collecting_cases_claim_letter cccl ON cccl.case_id = ct.collectingcase_id AND cccl.rest_note = 1";
                
                $sql_where .= " AND IFNULL(cccl.created, '0000-00-00') > DATE_SUB(ct.next_step_date, INTERVAL 14 DAY) ";
            } else if($sublist_filter == "sent_by_mistake"){
                $sql_join .= " JOIN collecting_cases cc ON cc.id = ct.collectingcase_id";
                $sql_where .= " AND cc.sent_by_mistake = 1";
            }
        } else if($list_filter == "search_by_invoice_tab"){
            if(!$active_search_filter){
                $sql_where .= " AND 1=2";
            } else {
                $sql_where_open_status = "";
            }
        } else if($list_filter == "next_step_collecting_company_case") {
            $sql_where .= " AND (ct.tab_status = 8 OR (ct.tab_status = 2 AND ct.next_step_is_oflow = 1))";
        } else {
            //invalidate if list filter not correct
            $sql_where .= " AND 1=2";
        }


		$sql_select .= ",ct.next_step_name as nextStepName, ct.next_step_action as nextStepActionType, 
            ct.next_step_is_collecting, ct.next_step_is_oflow,
            ct.next_step_date as nextStepDate,
		    ct.customer_name as debitorName,
			ct.customer_invoice_email as invoiceEmail, 
            ct.customer_invoice_phone as phone, 
            ct.current_due_date as currentStepDate,
            ct.collecting_case_original_claim as totalSumOriginalClaim, 
            'reminderLevel' as case_type,
            ct.case_choose_progress_of_reminderprocess,
            ct.case_choose_move_to_collecting_process,
            ct.case_profile_id as caseProfileId,
            IFNULL(ct.case_profile_id, ct.reminder_profile_id) as reminder_profile_id,
            ct.has_other_negative_amount_transactions";
        
		$sql_join .= "";
      
        if($list_filter != "allTransactionsWithoutCases") {
            $sql_where .= $sql_where_open_status." AND ct.system_type='InvoiceCustomer'";
	    }
	    $sql_where .= " AND (ct.comment is null OR ct.comment NOT LIKE '%\_%')";
  } else {
	  if($filter == "all_transactions"){
		  $sql_order = " ORDER BY ct.date_changed DESC";
	  } else {
	      $sql_where .= " AND ct.open = 1";
		  $sql_order = " ORDER BY ct.date_changed DESC";
	  }
	  foreach($filters as $filterName=>$filterValue) {
		  switch($filterName){
			  case "search_filter":
				  if($filterValue != ""){
					  $sql_join .= "";
					  $sql_where .= " AND (c.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR c.middlename LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR c.lastname LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR ct.invoice_nr LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
				  }
			  break;
		  }
	  }
	  $sql_join .= " JOIN customer c ON c.creditor_id = ct.creditor_id AND c.creditor_customer_id = ct.external_customer_id";
  }
  $s_sql_select_always = "";
  
  // var_dump(date("H:i:s"));
    $sql_company = "SELECT ".($b_countonly?"ct.id":" ct.*, ct.creditor_id as creditorCreditorId, ct.id as internalTransactionId, ct.due_date as transactionDueDate, cred.companyname as creditorName, ct.link_id".$sql_select)."".$s_sql_select_always." FROM creditor_transactions ct
            JOIN creditor cred ON cred.id = ct.creditor_id
            ".$sql_join."
            WHERE ct.content_status < 2 ".$creditor_sql."
            ".$sql_where.$status_sql;
    $sql_company .= " GROUP BY ct.id ".$sql_having;
    $sql = $sql_company;

    if($page == 0 && $perPage == 0 && !$show_all) {
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query) {
            $rowCount = $o_query->num_rows();
        }
        return $rowCount;
    } else {
        $sql .= $sql_order." ".$pager;
        $f_check_sql = $sql;
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $list = $o_query->result_array();
        }
        // if($_SERVER['REMOTE_ADDR'] == "83.99.234.99"){
        //     var_dump($o_main->db->last_query());
        // }
        return $list;
    }
}

function get_customers_with_negative_transactions($o_main, $cid) {
    
}