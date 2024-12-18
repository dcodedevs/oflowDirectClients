<?php
function get_customer_list_count2($o_main, $filter,$sub_filter, $filters){
    return get_customer_list($o_main, $filter, $sub_filter,$filters, 0, 0);
}
function get_customer_list_count($o_main, $filter,$sub_filter, $filters){
    $filters = array();
    return get_customer_list($o_main, $filter,$sub_filter, $filters, 0, 0);
}
function get_customer_list($o_main, $filter, $sub_filter, $filters, $page=1, $perPage=100, $customer_id = null) {

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();
	$sql_select = "";
    $sql_join = " LEFT OUTER JOIN collecting_cases_objection obj ON obj.collecting_case_id = p.id AND IFNULL(obj.objection_closed_date, '0000-00-00') = '0000-00-00'";
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
    if($filter > 0) {
        if($filter == 1){
            $sql_where .= " AND (ct.open = 1) AND (obj.id is null)";
        } else if($filter == 2){
            $sql_where .= " AND (ct.open = 0)";

			// $sql_join .= " LEFT OUTER JOIN creditor_transactions ct2 ON ct2.link_id = ct.link_id AND IFNULL(ct2.link_id, '') <> '' AND ct2.creditor_id = ct.creditor_id AND ct2.comment LIKE '%\_%'";
			// $sql_select .= ", COUNT(ct2.id) as number_of_transactions, IFNULL(SUM(ct2.amount), 0) + ct.amount as totalSum, ct.amount";
		}
    }
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
    $sql_select_cred = ", ccc.original_main_claim, ccc.case_closed_date, ccc.case_closed_reason,
    DATE_ADD(IFNULL(ccc.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
        IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, ccc.due_date as currentStepDate, nextStep.id as nextStepId,
        ccc.forgivenAmountOnMainClaim,ccc.forgivenAmountExceptMainClaim,ccc.overpaidAmount,
        ccc.collecting_case_surveillance_date,ccc.collecting_case_manual_process_date,ccc.collecting_case_created_date, ccc.warning_case_created_date";
    $sql_join_cred = " LEFT JOIN collecting_company_cases ccc ON ccc.id = ct.collecting_company_case_id
     LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = ccc.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = ccc.collecting_process_id
     LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = ccc.collecting_process_id
   ";

    if($filter == "cases_objection"){
        $sql_where .= "  AND (ct.open = 1) AND obj.id is not null AND (obj.objection_closed_date = '0000-00-00' OR obj.objection_closed_date is null)";
    } else if($filter == "cases_transferred"){
        $sql_select .= $sql_select_cred;
        $sql_join .= $sql_join_cred;
		$sql_where .= " AND p.status = 2 AND p.sub_status = 5";
	} else if($filter=="all_cases") {
		$sql_where .= " ";
	} else if($filter=="cases_canceled") {        
        $sql_select .= $sql_select_cred;
        $sql_join .= $sql_join_cred;
		$sql_where .= " AND p.status = 2 AND IFNULL(p.sub_status, 0) = 15";
	} else if($filter=="missing_transaction") {
       
        $sql_select .= $sql_select_cred;
        $sql_join .= $sql_join_cred;
		$sql_where .= " AND ct.id is null AND IFNULL(p.sub_status, 0) <> 15";
	} else if($filter =="without_due_date"){
		$sql_where .= " AND (p.due_date = '0000-00-00' OR p.due_date is null OR p.due_date = '1970-01-01') AND ct.open = 1";
	}
	if($filter == 2) {
		if($sub_filter == "not_approved") {
			$sql_where .= " AND IFNULL(p.approved_for_report, 0) = 0";
		} else if($sub_filter == "approved") {
			$sql_where .= " AND p.approved_for_report = 1";
		}
	}
    // if($filter == "cases_on_reminderlevel"){
    //     $sql_where .= " AND (p.status = 0 or p.status is null OR p.status = 1 OR p.status = 2 OR p.status = 5  )";
    // }
    $sql_order = " ORDER BY p.created ";
    $sql_order_direction = " DESC ";

    if($filter == 2){
        $sql_order = " ORDER BY p.stopped_date ";
    }
    foreach($filters as $filterName=>$filterValue) {
        switch($filterName){
            case "search_filter":
                if($filterValue != ""){
                    $sql_join .= "";
                    $sql_where .= " AND (cred.companyname LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR c2.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.id LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
                }
            break;
            case "projecttype_filter":
                if(strpos($filterValue, "6_") !== false){
                    $internalTaskId = str_replace("6_", "", $filterValue);
                    $sql_join .= "";
                    $sql_where .= " AND p.projectType = 5 AND p.internal_repeating_task_id = ".$o_main->db->escape($internalTaskId);
                } else {
                    if($filterValue > 0){
                        $sql_join .= "";
                        $sql_where .= " AND p.projectType = ".$o_main->db->escape($filterValue-1);
                    }
                }
            break;
            case "date_from_filter":
                if($filterValue != "" && $filter == 2){
                    $sql_join .= "";
                    $sql_where .= " AND (DATE(p.stopped_date) >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($filterValue)))."')";
                }
            break;
			case "date_to_filter":
                if($filterValue != "" && $filter == 2){
                    $sql_join .= "";
                    $sql_where .= " AND (DATE(p.stopped_date) <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($filterValue)))."')";
                }
            break;
			case "case_filter":
				if($filterValue > 0) {
					$sql_join .= " LEFT JOIN collecting_company_cases ccc ON ccc.id = ct.collecting_company_case_id";
					$sql_where .= " AND ccc.id is not null";
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
                    case "stopped_date";
                        $sql_order = " ORDER BY p.stopped_date ";
                    break;
                }
            break;
        }
    }
    $sql = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName, c2.paCountry as debitorCountry, ct.link_id, ct.creditor_id, ct.invoice_nr, ct.collecting_company_case_id, ct.amount".$sql_select."
             FROM collecting_cases p
             JOIN creditor cred ON cred.id = p.creditor_id
             JOIN customer c2 ON c2.id = p.debitor_id
             JOIN creditor_transactions ct ON ct.collectingcase_id = p.id
			 ".$sql_join."
            WHERE p.content_status < 2 ".$sql_where;
    if($customer_id != null) {
        $list = array();
        $sql .= " GROUP BY p.id".$sql_order.$sql_order_direction.$pager;
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $customerList = $o_query->result_array();
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
        if($page == 0 && $perPage == 0){
            $rowCount = 0;
            $o_query = $o_main->db->query($sql);
            if($o_query){
                $rowCount = $o_query->num_rows();
            }
            return $rowCount;
        } else {
            $sql .= $sql_order.$sql_order_direction.$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
			// var_dump($o_main->db->last_query());
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
