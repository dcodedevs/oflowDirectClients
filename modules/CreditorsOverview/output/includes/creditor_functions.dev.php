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
				   $sql_where .= " AND (c2.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.id LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
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
  $sql = "SELECT ".($b_countonly?"p.id":"p.*, cred.companyname as creditorName, c2.name as debitorName,
	  DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		  IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType,
		  step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId")."
		   FROM collecting_company_cases p
		   JOIN creditor cred ON cred.id = p.creditor_id
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

function get_transaction_count2($o_main, $cid, $filter, $filters){
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
       foreach($filters as $filterName=>$filterValue) {
           switch($filterName){
               case "search_filter":
                   if($filterValue != ""){
                       $sql_join .= "";
                       $sql_where .= " AND (c.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR c.middlename LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR c.lastname LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR ct.invoice_nr LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
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
               case "list_filter": {
                   $list_filter = $filterValue;
				   $doNotSendFilter = " AND (
					   (IFNULL(IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess), 0) <> 3 AND IFNULL(IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess), 0) <> 0)
					   OR ((IFNULL(IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess), 0) = 0
					   AND (IFNULL(c.choose_progress_of_reminderprocess, 0) <> 3)))
				   ) AND IF(cred.reminder_only_from_invoice_nr IS NULL or cred.reminder_only_from_invoice_nr = '', 0, cast(cred.reminder_only_from_invoice_nr as signed)) < IFNULL(cast(ct.invoice_nr as signed), 0)";
                   //From customer portal
                   if($filterValue == "canSendReminderNow") {
                        if($creditor['show_transfer_to_collecting_company_in_ready_to_send']) {

                            $sql_where .= " AND 
                            (
                                DATE_ADD(IFNULL(cc.due_date, ct.due_date), 
                                    INTERVAL IF(next_crcpv.days_after_due_date = '' OR next_crcpv.days_after_due_date IS null, IFNULL(nextStep.days_after_due_date, 0), next_crcpv.days_after_due_date) 
                                    DAY
                                ) <= NOW()
                            )".$doNotSendFilter;
                            
                        } else {
                            if($creditor['skip_reminder_go_directly_to_collecting']) {
                                $sql_where .= " AND (
                                        (cc.id is not null 
                                            AND DATE_ADD(IFNULL(cc.due_date, ct.due_date), 
                                                INTERVAL IF(next_crcpv.days_after_due_date = '' OR next_crcpv.days_after_due_date IS null, IFNULL(cccps.days_after_due_date, 0), next_crcpv.days_after_due_date)
                                                DAY
                                            ) <= NOW() 
                                            AND (cc.onhold_by_creditor is null OR  cc.onhold_by_creditor = 0) 
                                            AND nextStep.id is not null 
                                            AND (obj.id is null)
                                        ) OR (nextStep.id is null AND IFNULL(cccp.with_warning, 0) = 1)
                                    )".$doNotSendFilter;
                            } else {
                                $sql_where .= " AND (
                                    (cc.id is null) 
                                    OR profile.id is null 
                                    OR (cc.id is not null 
                                        AND DATE_ADD(IFNULL(cc.due_date, ct.due_date), 
                                            INTERVAL IF(next_crcpv.days_after_due_date = '' OR next_crcpv.days_after_due_date IS null, IFNULL(nextStep.days_after_due_date, 0), next_crcpv.days_after_due_date) 
                                            DAY) <= NOW()
                                        AND (cc.onhold_by_creditor is null OR  cc.onhold_by_creditor = 0) 
                                        AND nextStep.id is not null 
                                        AND (obj.id is null)
                                    ) OR (nextStep.id is null AND IFNULL(cccp.with_warning, 0) = 1)
                                )".$doNotSendFilter;
                            }
                        }
				   } else if($filterValue == "dueDateNotExpired"){
                   		$sql_where .= " AND cc.id is not null AND DATE_ADD(IFNULL(cc.due_date, ct.due_date), INTERVAL (IF(nextStep.id is null, IFNULL(step2.days_after_due_date, 0),IF(next_crcpv.days_after_due_date = '' OR next_crcpv.days_after_due_date IS null, IFNULL(nextStep.days_after_due_date, 0), next_crcpv.days_after_due_date))) DAY) > NOW()  AND (cc.onhold_by_creditor is null OR  cc.onhold_by_creditor = 0) AND (obj.id is null)".$doNotSendFilter;

				   } else if($filterValue == "doNotSend"){
                       $sql_where .= " AND (
						   IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess) = 3
						   OR (IFNULL(IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess), 0) = 0
						   AND (c.choose_progress_of_reminderprocess = 3 OR (IFNULL(c.choose_progress_of_reminderprocess, 0) = 0 AND cred.choose_progress_of_reminderprocess = 2)))
						   OR cred.reminder_only_from_invoice_nr >= ct.invoice_nr
				   	   )";
                   } else if($filterValue == "stoppedWithObjection"){
                       $sql_where .= " AND (obj.id is NOT NULL) AND  (obj.objection_closed_date = '0000-00-00' OR obj.objection_closed_date is null)";
                   } else if($filterValue == "notPayedConsiderCollectingProcess") {
                    
                        if($creditor['show_transfer_to_collecting_company_in_ready_to_send']) {
                            //when this config activated there shouldn't be anything in this tab
                            $sql_where .= " AND 1=0";
                        } else {
                            if($creditor['skip_reminder_go_directly_to_collecting']) {
                                $sql_where .= " AND ((cc.id is not null AND nextStep.id is null) OR cc.id is null) 
                                    AND DATE_ADD(
                                        IFNULL(cc.due_date, ct.due_date), 
                                        INTERVAL IFNULL(cccps.days_after_due_date, 0) DAY
                                    ) < NOW() 
                                    AND IFNULL(cccp.with_warning, 0) = 0
                                    AND (cc.onhold_by_creditor is null OR  cc.onhold_by_creditor = 0) 
                                    AND (obj.id is null)";
                            } else {
                                $sql_where .= " AND (profile.id IS NOT NULL AND nextStep.id is null) 
                                    AND DATE_ADD(
                                        IFNULL(cc.due_date, ct.due_date), 
                                        INTERVAL 
                                        IF(profile.specify_days_here, IFNULL(profile.days_after_due_date_move_to_collecting, 0), IFNULL(stepProcess.days_after_due_date_move_to_collecting, 0))
                                        DAY
                                    ) < NOW() 
                                    AND (cc.onhold_by_creditor is null OR  cc.onhold_by_creditor = 0) 
                                    AND (obj.id is null)
                                    AND IFNULL(cccp.with_warning, 0) = 0";
                            }
                        }
                   } else if($filterValue == "finishedOnReminderLevel"){
                       $sql_where .= " AND ct.open = 0 AND cc.id is not null AND (ct.collecting_company_case_id is null OR ct.collecting_company_case_id = 0)";
                   } else if($filterValue == "movedToCollectingLevel"){
                       $sql_where .= " AND cc.id is not null AND ct.collecting_company_case_id > 0";
                   } else if($filterValue == "allTransactionsWithoutCases"){
                   		$sql_where .= " AND IFNULL(ct.collectingcase_id, 0) = 0 AND IFNULL(ct.collecting_company_case_id, 0) = 0";
				   } else if($filterValue == "search_invoice") {

				   }
               }
               case "sublist_filter": {
                    if($list_filter == "canSendReminderNow" || $list_filter == "notPayedConsiderCollectingProcess") {
                        if($filterValue=="manual_move") {
                            $sql_where .= " AND 
                            (
                                IF(nextStep.id is not null,
                                    IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess) = 1
                                    OR 
                                    (
                                        IF(cc.id is null, IFNULL(ct.choose_progress_of_reminderprocess, 0), IFNULL(cc.choose_progress_of_reminderprocess, 0)) = 0 AND 
                                        (
                                            c.choose_progress_of_reminderprocess = 1 OR 
                                            (
                                                IFNULL(c.choose_progress_of_reminderprocess, 0) = 0 AND IFNULL(cred.choose_progress_of_reminderprocess, 0) = 0
                                            )
                                        )
                                    ),
                                    IF(cc.id is null, ct.choose_move_to_collecting_process, cc.choose_move_to_collecting_process) = 1
                                    OR 
                                    (
                                        IF(cc.id is null, IFNULL(ct.choose_move_to_collecting_process, 0), IFNULL(cc.choose_move_to_collecting_process, 0)) = 0 AND 
                                        (
                                            c.choose_move_to_collecting_process = 1 OR 
                                            (
                                                IFNULL(c.choose_move_to_collecting_process, 0) = 0 AND IFNULL(cred.choose_move_to_collecting_process, 0) = 0
                                            )
                                        )
                                    )
                                )

                            ) 
                            AND 
                            (
                                (
                                    (IF(nextStep.id > 0, IFNULL(nextStep.sending_action, 0), IFNULL(ccps.sending_action, 0)) = 1 
                                        OR 
                                        (
                                            IF(nextStep.id > 0, nextStep.sending_action, ccps.sending_action) = 4 AND (c.invoiceEmail = '' or c.phone = '')
                                        )
                                    )
                                    AND concat_ws(c.paStreet, c.paPostalNumber, c.paCity) <> ''
                                ) OR IF(nextStep.id > 0, IFNULL(nextStep.sending_action, 0), IFNULL(ccps.sending_action, 0)) <> 1
                                
                            ) 
                            ";
                        } else if($filterValue=="automatic_move"){
                            $sql_where .= " AND 
                            (
                                IF(nextStep.id is not null,
                                    IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess) = 2
                                    OR 
                                    (
                                        IF(cc.id is null, IFNULL(ct.choose_progress_of_reminderprocess, 0), IFNULL(cc.choose_progress_of_reminderprocess, 0)) = 0 AND 
                                        (
                                            c.choose_progress_of_reminderprocess = 2 OR 
                                            (
                                                IFNULL(c.choose_progress_of_reminderprocess, 0) = 0 AND cred.choose_progress_of_reminderprocess = 1
                                            )
                                        )
                                    ),
                                    IF(cc.id is null, ct.choose_move_to_collecting_process, cc.choose_move_to_collecting_process) = 2
                                    OR 
                                    (
                                        IF(cc.id is null, IFNULL(ct.choose_move_to_collecting_process, 0), IFNULL(cc.choose_move_to_collecting_process, 0)) = 0 AND 
                                        (
                                            c.choose_move_to_collecting_process = 2 OR 
                                            (
                                                IFNULL(c.choose_move_to_collecting_process, 0) = 0 AND cred.choose_move_to_collecting_process = 1
                                            )
                                        )
                                    )
                                )
                            ) 
                            AND 
                            (
                                (
                                    (IF(nextStep.id > 0, IFNULL(nextStep.sending_action, 0), IFNULL(ccps.sending_action, 0)) = 1 
                                        OR 
                                        (
                                            IF(nextStep.id > 0, nextStep.sending_action, ccps.sending_action) = 4 AND (c.invoiceEmail = '' or c.phone = '')
                                        )
                                    )
                                    AND concat_ws(c.paStreet, c.paPostalNumber, c.paCity) <> ''
                                ) OR IF(nextStep.id > 0, IFNULL(nextStep.sending_action, 0), IFNULL(ccps.sending_action, 0)) <> 1
                                
                            )                             
                            ";
                        } else if($filterValue=="missing_address") {
                            $sql_where .= " AND 
                            (
                                IF(nextStep.id > 0, IFNULL(nextStep.sending_action, 0), IFNULL(ccps.sending_action, 0)) = 1 
                                OR 
                                (
                                    IF(nextStep.id > 0, nextStep.sending_action, ccps.sending_action) = 4 AND (c.invoiceEmail = '' or c.phone = '')
                                )
                            ) AND concat_ws(c.paStreet, c.paPostalNumber, c.paCity) = ''";
                        }
                    }
               }
           }
       }


		$sql_select .= ", cc.*,c.customer_type_collect,  c.customer_type_collect_addition, ccps.days_after_due_date,
		DATE_ADD(IFNULL(cc.due_date, ct.due_date), INTERVAL IFNULL(IF(next_crcpv.days_after_due_date = '' OR next_crcpv.days_after_due_date IS null, IFNULL(nextStep.days_after_due_date, 0), next_crcpv.days_after_due_date), IF(crcpv.days_after_due_date = '' OR crcpv.days_after_due_date IS null, IFNULL(ccps.days_after_due_date, 0), crcpv.days_after_due_date)) DAY) as nextStepDate,
			   CONCAT_WS(' ',c.name, c.middlename, c.lastname) as debitorName,
			   IF(nextStep.id > 0, nextStep.name, ccps.name) as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, ccps.sending_action) as nextStepActionType,
			   c.invoiceEmail, c.phone, step2.name as processStepName, IFNULL(cc.due_date, ct.due_date) as currentStepDate,
					   nextStep.id as nextStepId, IFNULL(nextStep.id, ccps.id) as nextActionStepId,
					   ct.collecting_case_original_claim as totalSumOriginalClaim, 'reminderLevel' as case_type, IFNULL(IF(cc.id is null, ct.choose_progress_of_reminderprocess, cc.choose_progress_of_reminderprocess), 0) as choose_progress_of_reminderprocess, profile.id as caseProfileId,
                       profilePerson.id as profilePersonId, profileCompany.id as profileCompanyId";
        $sql_select.=', IF(IFNULL(profile.collecting_process_move_to, 0) = 0, IFNULL(stepProcess.collecting_process_move_to,0), profile.collecting_process_move_to) as collectingProcessToMoveTo';
        
		$sql_join .= "
		LEFT JOIN customer c ON c.creditor_customer_id = ct.external_customer_id AND c.creditor_id = ct.creditor_id
		LEFT JOIN collecting_cases cc ON cc.id = ct.collectingcase_id AND cc.debitor_id = c.id
		LEFT JOIN creditor_reminder_custom_profiles profile ON profile.id = cc.reminder_profile_id
		LEFT JOIN creditor_reminder_custom_profiles profilePerson ON profilePerson.id = cred.creditor_reminder_default_profile_id
		LEFT JOIN creditor_reminder_custom_profiles profileCompany ON profileCompany.id = cred.creditor_reminder_default_profile_for_company_id

		LEFT JOIN collecting_cases_process ccp ON ((ccp.id = profileCompany.reminder_process_id AND (((c.customer_type_collect is null OR c.customer_type_collect = 0) AND (c.customer_type_collect_addition = 0 OR c.customer_type_collect_addition is null)) OR c.customer_type_collect_addition = 1))
		OR (ccp.id = profilePerson.reminder_process_id AND ((c.customer_type_collect = 1 AND (c.customer_type_collect_addition = 0 OR c.customer_type_collect_addition is null)) OR c.customer_type_collect_addition = 2 )))
		LEFT JOIN collecting_cases_process_steps ccps ON ccps.collecting_cases_process_id = ccp.id
		LEFT JOIN collecting_cases_process_steps AS filter
		  ON filter.collecting_cases_process_id = ccps.collecting_cases_process_id
		  AND filter.id < ccps.id

        LEFT JOIN creditor_reminder_custom_profile_values crcpv ON crcpv.collecting_cases_process_step_id = ccps.id AND ((crcpv.creditor_reminder_custom_profile_id = profileCompany.id AND (((c.customer_type_collect is null OR c.customer_type_collect = 0) AND (c.customer_type_collect_addition = 0 OR c.customer_type_collect_addition is null)) OR c.customer_type_collect_addition = 1))
		OR (crcpv.creditor_reminder_custom_profile_id = profilePerson.id AND ((c.customer_type_collect = 1 AND (c.customer_type_collect_addition = 0 OR c.customer_type_collect_addition is null)) OR c.customer_type_collect_addition = 2 )))

		LEFT JOIN collecting_cases_process_steps step2 ON step2.id = cc.collecting_cases_process_step_id AND step2.collecting_cases_process_id = profile.reminder_process_id
		LEFT JOIN collecting_cases_process stepProcess ON step2.collecting_cases_process_id = stepProcess.id
        LEFT JOIN collecting_cases_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_process_id = profile.reminder_process_id
        LEFT JOIN creditor_reminder_custom_profile_values next_crcpv ON next_crcpv.collecting_cases_process_step_id = nextStep.id AND next_crcpv.creditor_reminder_custom_profile_id = profile.id
        LEFT JOIN collecting_cases_collecting_process cccp ON cccp.id = IF(IFNULL(cred.collecting_process_to_move_from_reminder, 0) = 0, ".$collecting_system_settings['default_collecting_process_to_move_from_reminder'].", cred.collecting_process_to_move_from_reminder)
        LEFT JOIN collecting_cases_collecting_process_steps cccps ON cccps.collecting_cases_collecting_process_id = cccp.id
		LEFT JOIN collecting_cases_collecting_process_steps AS filter2
		  ON filter2.collecting_cases_collecting_process_id = cccps.collecting_cases_collecting_process_id
		  AND filter2.id < cccps.id
        ";
		$sql_join .= " LEFT JOIN collecting_cases_objection obj ON obj.collecting_case_id = cc.id AND (obj.objection_closed_date = '0000-00-00' or obj.objection_closed_date is null)";
      if($creditor['id'] == 1041 || $creditor['id'] == 1031){
        // $sql_join .= " LEFT JOIN creditor_transactions connected_transactions ON connected_transactions.open = 1 AND connected_transactions.link_id IS NOT NULL AND connected_transactions.link_id = ct.link_id AND connected_transactions.creditor_id = ct.creditor_id AND connected_transactions.id <> ct.id";
      }
       if($list_filter != "allTransactionsWithoutCases") {
            if($creditor['id'] == 1041 || $creditor['id'] == 1031 ){
                $sql_where .= " AND filter.id is NULL AND filter2.id IS NULL  AND (
                    (ct.open = 1) AND (ct.system_type='InvoiceCustomer')	          
                )";
            } else {
                $sql_where .= " AND filter.id is NULL AND filter2.id IS NULL  AND (
                (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer')	          
                )";
            }
            $sql_where .= "         
            AND DATE_ADD(ct.due_date, 
                INTERVAL IF(crcpv.days_after_due_date = '' OR crcpv.days_after_due_date IS null, IFNULL(ccps.days_after_due_date, 0), crcpv.days_after_due_date) 
                DAY) <= NOW()";        
		    $sql_where .= " AND (ct.collecting_company_case_id IS NULL or ct.collecting_company_case_id = 0)";
	    }
	  $sql_where .= " AND (ct.comment is null OR ct.comment NOT LIKE '%\_%')";
  } else {
	  if($filter == "all_transactions") {
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
  if($creditor['id'] == 1041 || $creditor['id'] == 1031){
    // $sql_having = " HAVING totalAmount > 0";
  }
  
  $s_sql_select_always = "";
  if($creditor['id'] == 1041 || $creditor['id'] == 1031){
    // $s_sql_select_always = ", (ct.amount+ IFNULL(SUM(connected_transactions.amount), 0)) as totalAmount";
  }
  // var_dump(date("H:i:s"));
    $sql_company = "SELECT ".($b_countonly?"ct.id":" ct.*, ct.creditor_id as creditorCreditorId, ct.id as internalTransactionId, ct.due_date as transactionDueDate, cred.companyname as creditorName, ct.link_id".$sql_select)."".$s_sql_select_always." FROM creditor_transactions ct
             LEFT JOIN creditor cred ON cred.id = ct.creditor_id
            ".$sql_join."
            WHERE ct.content_status < 2 ".$creditor_sql."
            ".$sql_where.$status_sql;
    $sql_company .= " GROUP BY ct.id ".$sql_having;
    $sql = $sql_company;

    if($page == 0 && $perPage == 0 && !$show_all){
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query){
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
        if($_SERVER['REMOTE_ADDR'] == "83.99.234.99"){
            var_dump($o_main->db->last_query());
        }
        return $list;
    }
}
