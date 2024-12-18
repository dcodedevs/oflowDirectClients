<?php
function get_customer_list_count2($o_main, $filter, $filter_main, $filters){
    return get_customer_list($o_main, $filter, $filter_main, $filters, 0, 0);
}
function get_customer_list_count($o_main, $filter, $filter_main, $filters){
    foreach($filters as $filterName=>$filterValue){
        if($filterName == 'search_filter'){
            unset($filters[$filterName]);
        }
    }
    return get_customer_list($o_main, $filter, $filter_main, $filters, 0, 0);
}
function get_customer_list($o_main, $filter, $filter_main, $filters, $page=1, $perPage=100, $customer_id = null) {
    global $variables;
    $sql_select = "";
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
    $sql_order_by = " ORDER BY p.created ".$sql_order_direction;

    $userId = 0;
    foreach($filters as $filterName=>$filterValue){
        switch($filterName){
            case "responsibleperson_filter":
                if($filterValue > 0){
                    // $sql_join .= "";
                    // $sql_where .= " AND p.projectLeader = ".$o_main->db->escape($filterValue);
                    $userId = $filterValue;
                }
            break;
            case "search_filter":
                if($filterValue[1] != "") {
                    $sql_where .= " AND (p.subject LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR c.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
                }
            break;
            case "casetype_filter":
                if($filterValue > 0){
                    $sql_join .= "";
                    $sql_where .= " AND p.case_type = ".$o_main->db->escape($filterValue);
                }
            break;
            case "order_field":
                switch($filterValue) {
                    case "created";
                        $sql_order_by = " ORDER BY p.created ".$sql_order_direction;
                    break;
                    case "completed";
                        $sql_order_by = " ORDER BY p.completed_date ".$sql_order_direction;
                    break;
                    case "project_name";
                        $sql_order_by = " ORDER BY p.name ".$sql_order_direction;
                    break;
                    case "customer_name";
                        $sql_order_by = " ORDER BY customerName".$sql_order_direction;
                    break;
                    case "subject";
                        $sql_order_by = " ORDER BY p.subject ".$sql_order_direction;
                    break;
                    case "responsible";
                        $sql_order_by = " ORDER BY projectLeaderName ".$sql_order_direction;
                    break;
                    case "type";
                        $sql_order_by = " ORDER BY p.case_type ".$sql_order_direction;
                    break;
                    case "last_message";
                        $sql_order = " ORDER BY lastMessageDate ".$sql_order_direction;
                    break;
                    case "access";
                        $sql_order_by = " ORDER BY p.case_access ".$sql_order_direction;
                    break;
                    default:
                        $sql_order_by = " ORDER BY lastMessageDate".$sql_order_direction;
                    break;
                }
            break;
        }
    }

    $people_contactperson_type = 2;
    $sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
    $o_query = $o_main->db->query($sql);
    $accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
    if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
    	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
    }

    if($filter == "active"){
        $sql_where .= " AND (p.status = 0 or p.status is null )";
    }
    if($filter == "interrupted"){
        $sql_where .= " AND p.status = 2";
    }
    if($filter == "completed"){
        $sql_where .= " AND p.status = 1";
    }

    $sql = "SELECT p.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) as customerName".$sql_select."
             FROM collecting_cases_payment_plan p
             LEFT JOIN collecting_cases cc ON cc.id = p.collecting_case_id
             LEFT JOIN customer c ON c.id =  cc.debitor_id
            ".$sql_join."
            WHERE 1=1".$sql_where;
            // var_dump($sql);
    if($customer_id != null){
        $list = array();
        $sql .= " GROUP BY p.id ".$sql_order_by." ASC".$pager;

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
            $sql .= " ".$sql_order_by.$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
