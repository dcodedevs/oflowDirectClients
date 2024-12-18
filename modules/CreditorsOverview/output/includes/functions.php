<?php
function get_customer_list_count2($o_main, $filter, $filters){
    return get_customer_list($o_main, $filter, $filters, 0, 0);
}
function get_customer_list_count($o_main, $filter, $filters){
    $filters = array();
    return get_customer_list($o_main, $filter, $filters, 0, 0);
}
function get_customer_list($o_main, $filter, $filters, $page=1, $perPage=100, $customer_id = null) {

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_join = "";
    $sql_where = "";

    if($page == 0 && $perPage == 0){
        $pager = "";
    }
	$sql_order_direction = " DESC";
    foreach($filters as $filterName=>$filterValue) {
        switch($filterName){
            case "order_direction":
                switch($filterValue) {
                    case "0";
                        $sql_order_direction = " DESC";
                    break;
                    case "1";
                        $sql_order_direction = " ASC";
                    break;
                }
            break;
        }
    }

    foreach($filters as $filterName=>$filterValue){
        switch($filterName){
            case "search_filter":
                if($filterValue != ""){
                    $sql_join .= "";
                    $sql_where .= " AND (p.companyname LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.id LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
                }
            break;
			case "order_field":
                switch($filterValue) {
                    case "created";
                        $sql_order = " ORDER BY p.created ".$sql_order_direction;
                    break;

                    case "creditorname";
                        $sql_order = " ORDER BY p.companyname ".$sql_order_direction;
                    break;
                    case "autosync";
                        $sql_order = " ORDER BY IFNULL(p.autosyncing_not_working_date, '0000-00-00 00:00:00') ".$sql_order_direction;
                    break;
                    case "controlsum";
                        $sql_order = " ORDER BY IFNULL(p.control_sum_correct, '0000-00-00 00:00:00') ".$sql_order_direction;
                    break;
                    case "demo";
                        $sql_order = " ORDER BY IFNULL(p.is_demo, 0) ".$sql_order_direction;
                    break;
                    case "autosync";
                        $sql_order = " ORDER BY IFNULL(p.autosyncing_not_working_date, '0000-00-00 00:00:00') ".$sql_order_direction;
                    break;
                    case "skip";
                        $sql_order = " ORDER BY IFNULL(p.skip_reminder_go_directly_to_collecting, 0) ".$sql_order_direction;
                    break;
                    case "reminder_from";
                        $sql_order = " ORDER BY IFNULL(p.reminder_only_from_invoice_nr, 0) ".$sql_order_direction;
                    break;
                    default:
                        $sql_order = " ORDER BY p.created ".$sql_order_direction;
                    break;
                }
            break;
        }
    }
	if($filter == 1){
		$sql_where .= " AND choose_progress_of_reminderprocess = 1";
	} else if($filter == 2){
		$sql_where .= " AND choose_move_to_collecting_process = 1";
	} else if($filter == 3){
		$sql_where .= " AND onboarding_incomplete = 1";
	} else if($filter == 4){
		$sql_where .= " AND IFNULL(p.control_sum_correct, '0000-00-00 00:00:00') = '0000-00-00 00:00:00' AND onboarding_incomplete = 0 AND sync_from_accounting = 1";
	} else if($filter == 5){
		$sql_where .= " AND is_demo = 1";
	} else if($filter == 6){
		$sql_where .= " AND IFNULL(collecting_agreement_accepted_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
	} else if($filter == 7){
		$sql_where .= " AND IFNULL(autosyncing_not_working_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
	} else if($filter == 8){
		$sql_where .= " AND IFNULL(autosyncing_not_working_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' AND choose_progress_of_reminderprocess = 1";
	} else if($filter == 9){
		$sql_where .= " AND IFNULL(skip_reminder_go_directly_to_collecting, 0) = 1";
	} else if($filter == 10){
		$sql_join.=" LEFT OUTER JOIN collecting_company_cases ccc ON ccc.creditor_id = p.id";
		$sql_where .= "  AND IFNULL(collecting_agreement_accepted_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00' AND ccc.id is not null";
	} else if($filter == 11){
		$sql_join.="";
		$sql_where .= "  AND IFNULL(skip_reminder_go_directly_to_collecting, 0) = 2";
	} else if($filter == 12){
		$sql_join.="";
		$sql_where .= "  AND IFNULL(show_transfer_to_collecting_company_in_ready_to_send, 0) = 1";
	} else if($filter == 13) {
		$sql_join.="";
		$sql_where .= "  AND IFNULL(activate_send_reminders_by_ehf, 0) = 1";
	} else if($filter == 14) {
		$sql_join.=" 
        LEFT JOIN creditor_reminder_custom_profiles ON creditor_reminder_custom_profiles.id = p.creditor_reminder_default_profile_id 
        LEFT JOIN creditor_reminder_custom_profiles crcp_company ON crcp_company.id = p.creditor_reminder_default_profile_for_company_id 
        ";
		$sql_where .= "  AND IFNULL(creditor_reminder_custom_profiles.reminder_process_id, 0) = 16
        AND IFNULL(crcp_company.reminder_process_id, 0) NOT IN (12, 13,14)";
	} else if($filter == 15) {
		$sql_join.="";
		$sql_where .= "  AND IFNULL(collecting_process_to_move_from_reminder, 0) > 0";
	} else if($filter == 16) {
		$sql_join.="";
		$sql_where .= "  AND IFNULL(billing_type, 0) > 0";
	}

	// $sql_order = " ORDER BY p.id ASC";
    $sql = "SELECT p.*, p.companyname as creditorName
             FROM creditor p
            ".$sql_join."
            WHERE p.content_status < 2 ".$sql_where;
    if($customer_id != null){
        $list = array();
        $sql .= " GROUP BY p.id ".$sql_order." ".$pager;

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
            $sql .= $sql_order." ".$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
