<?php
function get_customer_list_count2($o_main, $filter, $filters){
    return get_customer_list($o_main, $filter, $filters, 0, 0);
}
function get_customer_list_count($o_main, $filter, $filters){
    $filters = array();
    return get_customer_list($o_main, $filter, $filters, 0, 0);
}
function get_customer_list($o_main, $filter, $filters, $page=1, $perPage=1000, $customer_id = null) {

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();
    $sql_join = "";
    // $sql_join = " LEFT OUTER JOIN collecting_cases_objection obj ON obj.collecting_company_case_id = p.id AND (obj.objection_closed_date = '0000-00-00' or obj.objection_closed_date is null)";
	// $sql_join .= " LEFT OUTER JOIN collecting_company_cases_returned_letter returned ON returned.collecting_company_case_id = p.id AND (IFNULL(returned.solved, 0) = 0)";
	$sql_join .= " LEFT OUTER JOIN collecting_company_case_paused paused ON paused.collecting_company_case_id = p.id AND IFNULL(paused.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
    $s_sql_select = "";
    if($filter != "all") {
        $s_sql_select .= ",
        DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
        IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId,
        nextContinuingStep.appear_in_legal_step_handling, nextContinuingStep.appear_in_call_debitor_step_handling,
        DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextContinuingStep.days_after_due_date, 0) DAY) as nextContinuingStepDate,
        IF(nextContinuingStep.id > 0, nextContinuingStep.name, '') as nextContinuingStepName";
        $sql_join .= "
        LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
        LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
        LEFT JOIN collecting_company_cases_continuing_process_steps currentContinuingStep ON currentContinuingStep.id = p.continuing_process_step_id            
        LEFT JOIN collecting_company_cases_continuing_process_steps nextContinuingStep ON nextContinuingStep.sortnr = (IFNULL(currentContinuingStep.sortnr, 0)+1) AND nextContinuingStep.collecting_company_cases_continuing_process_id = currentContinuingStep.collecting_company_cases_continuing_process_id
       ";
    }
	$sql_where = "";

    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    // if($filter == "active"){
    //     $sql_where .= " AND (p.status = 0 or p.status is null ) AND (obj.id is null)";
    // }
    if($filter == "active") {
        $filter = 1;
    }
    // if($filter > 0) {
    //     if($filter == 1){
    //         $sql_where .= " AND (p.status = '".$filter."' OR p.status = 0 OR p.status is null)";
    //     } else {
    //         $sql_where .= " AND (p.status = '".$filter."')";
    //     }
    // }
    // if($filter == "inactive"){
    //     $sql_join .= "";
    //     $sql_where .= " AND p.status = 4";
    // }
    // if($filter == "finished"){
    //     $sql_where .= " AND (p.status = 1 AND obj.id is null) AND (obj.id is null)";
    // }
    // if($filter == "objection"){
    //     $sql_join .= "";
    //     $sql_where .= " AND p.status = 2";
    // }
    // if($filter == "canceled"){
    //     $sql_join .= "";
    //     $sql_where .= " AND p.status = 3";
    // }
    // if($filter == "cases_objection"){
    //     $sql_where .= "  AND (p.status = 0 or p.status is null or p.status = 1) AND obj.id is not null AND (obj.objection_closed_date = '0000-00-00' OR obj.objection_closed_date is null)";
    // }
    // if($filter == "cases_on_reminderlevel"){
    //     $sql_where .= " AND (p.status = 0 or p.status is null OR p.status = 1 OR p.status = 2 OR p.status = 5  )";
    // }
    // if($filter == "cases_on_collectinglevel"){
    //     $sql_where .= " AND (p.status = 3 OR p.status = 4 OR p.status = 6 OR p.status = 7)";
    // }
    $sql_order = " ORDER BY p.id ";
    $sql_order_direction = " DESC ";
	$content_stats_sql = " p.content_status < 2";
	if($filter == "warning") {
		$sql_where .= " AND IFNULL(p.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND IFNULL(p.collecting_case_created_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.case_closed_date, '0000-00-00') = '0000-00-00'";
	} else if($filter == "warning_closed") {
		$sql_where .= " AND IFNULL(p.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND IFNULL(p.collecting_case_created_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.case_closed_date, '0000-00-00') <> '0000-00-00'";
	} else if($filter == "collecting"){
		$sql_where .= " AND (IFNULL(p.collecting_case_created_date, '0000-00-00') <> '0000-00-00' OR IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') <> '0000-00-00') AND IFNULL(p.case_closed_date, '0000-00-00') = '0000-00-00'";
	} else if($filter == "collecting_closed"){
		$sql_where .= " AND (IFNULL(p.collecting_case_created_date, '0000-00-00') <> '0000-00-00' OR IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') <> '0000-00-00') AND IFNULL(p.case_closed_date, '0000-00-00') <> '0000-00-00'";
	} else if($filter == "company_fee_paid"){
		$sql_where .= " AND p.company_fee_paid = 1";
	} else if($filter == "company_fee_notpaid"){
		$sql_where .= " AND p.company_fee_notpaid = 1";
	} else if($filter == "without_fee_paid"){
		$sql_where .= " AND p.without_fee_paid = 1";
	} else if($filter == "without_fee_notpaid"){
		$sql_where .= " AND p.without_fee_notpaid = 1";
	} else if($filter == "due_date_issue") {
		$sql_where .= " AND p.due_date_issue = 1";
	} else if($filter == "consider") {
		$claimline_type_ids = array();

		$query = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE make_to_appear_in_consider_tab = 1";
		$o_query = $o_main->db->query($query);
		$claimline_types = $o_query ? $o_query->result_array() : array();
		foreach($claimline_types as $claimline_type) {
			$claimline_type_ids[] = $claimline_type['id'];
		}
		$sql_where .= " AND cccl.claim_type IN (".implode(',',$claimline_type_ids).")";
	} else if($filter == "cases_to_check") {
		$sql_where .= " AND p.checkbox_1 = 1";
	} else if($filter == "deleted") {
		// $sql_where .= " AND p.content_status = 2";
		$content_stats_sql = " p.content_status = 2";
	} else if($filter == "currency_new_case") {
	   $sql_where .= " AND p.currency = 1";
   } else if($filter == "currency_recalculated") {
	   $sql_where .= " AND p.currency = 2";
	}
    $initial_call = true;
	$sql_where_total = "";
    foreach($filters as $filterName=>$filterValue) {
        switch($filterName){
            case "search_filter":
                if($filterValue != ""){
                    $initial_call = false;
                    $sql_join .= "";
                    $sql_where .= " AND (cred.companyname LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR c2.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.id LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
                }
            break;
			case "amount_from_filter":
                if($filterValue > 0){
                    $initial_call = false;
                    $sql_join .= "";
                    $sql_where_total .= " AND tableC.totalAmountValue >= '".$o_main->db->escape_like_str($filterValue)."'";
                }
            break;
			case "amount_to_filter":
                if($filterValue > 0){
                    $initial_call = false;
                    $sql_join .= "";
                    $sql_where_total .= " AND tableC.totalAmountValue <= '".$o_main->db->escape_like_str($filterValue)."'";
				}
            break;
			case "debitor_type_filter":
				if($filterValue == 1){
                    $initial_call = false;
					$sql_join .= "";
					$sql_where .= " AND (
						(IFNULL(c2.customer_type_for_collecting_cases, 0) = 0
						AND (IF(IFNULL(c2.customer_type_collect_addition, 0) = 0, IFNULL(c2.customer_type_collect, 0), 1) = 0 OR IFNULL(c2.customer_type_collect_addition, 0) = 1))
					OR c2.customer_type_for_collecting_cases = 1)";
				} else if($filterValue == 2){
                    $initial_call = false;
					$sql_join .= "";
					$sql_where .= " AND (
						(IFNULL(c2.customer_type_for_collecting_cases, 0) = 0
						AND (IF(IFNULL(c2.customer_type_collect_addition, 0) = 0, IFNULL(c2.customer_type_collect, 0), 0) = 1 OR IFNULL(c2.customer_type_collect_addition, 0) = 2) )
					OR c2.customer_type_for_collecting_cases = 2)";
				}
			break;
			case "cases_without_fee_filter":
				if($filterValue == 1){
                    $initial_call = false;
					$sql_join .= " LEFT JOIN collecting_company_cases_claim_lines cccl_fee ON cccl_fee.collecting_company_case_id = p.id
					AND (cccl_fee.claim_type = 4 OR cccl_fee.claim_type = 5 OR cccl_fee.claim_type = 6 OR cccl_fee.claim_type = 7)
					AND p.collecting_cases_process_step_id > 0";
					$sql_where .= " AND (cccl_fee.id is null)";
				}
			break;
            case "projecttype_filter":
                if(strpos($filterValue, "6_") !== false){
                    $initial_call = false;
                    $internalTaskId = str_replace("6_", "", $filterValue);
                    $sql_join .= "";
                    $sql_where .= " AND p.projectType = 5 AND p.internal_repeating_task_id = ".$o_main->db->escape($internalTaskId);
                } else {
                    if($filterValue > 0){
                        $initial_call = false;
                        $sql_join .= "";
                        $sql_where .= " AND p.projectType = ".$o_main->db->escape($filterValue-1);
                    }
                }
            break;
            case "sub_status_filter":
                if($filterValue > 0){
                    $initial_call = false;
                    $sql_join .= "";
                    $sql_where .= " AND p.sub_status = ".$o_main->db->escape($filterValue);
                }
            break;
            case "sublist_filter":
				if($filter == "warning" || $filter == "collecting"){
                    $initial_call = false;
					$autoprocess_only_sql = " AND IFNULL(p.collecting_case_surveillance_date, '0000-00-00') = '0000-00-00' AND IFNULL(p.collecting_case_manual_process_date, '0000-00-00') = '0000-00-00'";

				    if($filterValue == "canSendNow"){
						$sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND 
                       (
                        (nextStep.id is not null AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) <= NOW()) 
                        OR 
                        (nextContinuingStep.id is not null AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextContinuingStep.days_after_due_date, 0) DAY) <= NOW())
                        )

                        AND (p.onhold_by_creditor is null OR  p.onhold_by_creditor = 0) AND (paused.id is null)
                        ".$autoprocess_only_sql;
	                } else if($filterValue == "dueDateNotExpired") {
						$sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND p.id is not null AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) > NOW()  AND (p.onhold_by_creditor is null OR  p.onhold_by_creditor = 0) AND (paused.id is null)".$autoprocess_only_sql;
					} else if($filterValue == "stoppedWithObjection"){
	                    $sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND (obj.id is NOT NULL) AND  (obj.objection_closed_date = '0000-00-00' OR obj.objection_closed_date is null)".$autoprocess_only_sql;
	                } else if($filterValue == "notStarted"){
	                    $sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND (p.collecting_cases_process_step_id = 0 OR p.collecting_cases_process_step_id is null)".$autoprocess_only_sql;
	                } else if($filterValue == "manualProcess"){
	                    $sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND (IFNULL(p.collecting_case_manual_process_date, '0000-00-00') <> '0000-00-00' AND IFNULL(p.collecting_case_surveillance_date, '0000-00-00') = '0000-00-00')";
	                } else if($filterValue == "surveillance"){
	                    $sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND IFNULL(p.collecting_case_surveillance_date, '0000-00-00') <> '0000-00-00'";
	                } else if($filterValue == "paused"){
						$sql_where .= " AND IFNULL(p.currency, 0) <> 1 AND (paused.id is NOT NULL)";
					} else if($filterValue == "disputed"){
						$sql_where .= " AND p.dispute_case = 1";
					} else if($filterValue == "completed"){
						$sql_where .= " AND p.id is not null AND ((p.collecting_cases_process_step_id > 0 AND nextStep.id is null) OR (p.continuing_process_step_id > 0 AND nextContinuingStep.id is null))
                        AND p.due_date < CURDATE() AND (p.onhold_by_creditor is null OR  p.onhold_by_creditor = 0) AND (paused.id is null)".$autoprocess_only_sql;
					}
				}
            break;
			case "closed_reason_filter":
				if($filter == "warning_closed" || $filter == "collecting_closed") {
					if($filterValue != ""){
                        $initial_call = false;
						$sql_join .= "";
						$sql_where .= " AND p.case_closed_reason = ".$o_main->db->escape(intval($filterValue));
					}
				}
			break;
			case "show_not_zero_filter":
				$show_not_zero = intval($filterValue);
                $initial_call = false;
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
                    case "stopped_date";
                        $sql_order = " ORDER BY p.stopped_date ";
                    break;
                }
            break;
        }
    }
    $sql = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName, c2.paCountry as debitorCountry,c2.extra_language, 
            IFNULL(SUM(cccl.amount), 0) as totalAmountValue".$s_sql_select."
            FROM collecting_company_cases p
            JOIN creditor cred ON cred.id = p.creditor_id
            LEFT JOIN customer c2 ON c2.id = p.debitor_id
            LEFT JOIN collecting_company_cases_claim_lines cccl ON cccl.collecting_company_case_id = p.id
 			".$sql_join."
            WHERE ".$content_stats_sql." ".$sql_where;
    if($customer_id != null) {
        $list = array();
        $sql .= " GROUP BY p.id".$sql_order.$sql_order_direction.$pager;
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $customerList = $o_query->result_array();
            $currentCustomerIndex = 0;
            foreach($customerList as $index=>$customer) {
                if($customer['customerId'] == $customer_id) {
                    $currentCustomerIndex = $index;
                    break;
                }
            }
            array_push($list, $customerList[$currentCustomerIndex-1]);
            array_push($list, $customerList[$currentCustomerIndex]);
            array_push($list, $customerList[$currentCustomerIndex+1]);
        }

        return $list;
    } else {
        $sql .= " GROUP BY p.id";
        if($initial_call) {
            $sql_init = $sql;
        } else {
            $sql_init = "SELECT * FROM (".$sql.") tableC WHERE 1=1".$sql_where_total;
        }
        if($page == 0 && $perPage == 0){
            $sql_init = $sql;
            $rowCount = 0;
            $o_query = $o_main->db->query($sql_init);
            if($o_query){
                $rowCount = $o_query->num_rows();
            }
            return $rowCount;
        } else {           
            $sql .= $sql_order.$sql_order_direction;
			if(!$show_not_zero) {
	            $sql .= $pager;
			}
			$sql_init = "SELECT * FROM (".$sql.") tableC WHERE 1=1".$sql_where_total;
            $f_check_sql = $sql_init;
            $o_query = $o_main->db->query($sql_init);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            // if($_SESSION['username'] == "byamba@dcode.no"){                
            //     var_dump($o_main->db->last_query());
            // }
            return $list;
        }
    }
}
