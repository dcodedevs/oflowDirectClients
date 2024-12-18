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
    // $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_join = "";
    $sql_where = "";

    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    foreach($filters as $filterName=>$filterValue){
        switch($filterName){
            case "group_filter":
                if($filterValue > 0){
                    $sql_join .= " LEFT OUTER JOIN contactperson_group_user gu ON gu.contactperson_id = cp.id";
                    $sql_where .= " AND gu.contactperson_group_id = ".$o_main->db->escape($filterValue);
                } else if($filterValue == -1){
                    $sql_join .= " LEFT OUTER JOIN contactperson_group_user gu ON gu.contactperson_id = cp.id";
                    $sql_where .= " AND gu.id is null";
                }
            break;
            case "search_filter":
                if(is_array($filterValue)){
                     switch($filterValue[0]){
                        case 1:
                        $sql_join .= "";
                        $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.middlename LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.lastname LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
                        break;
                        case 2:
                        $sql_join .= "";
                        $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
                        break;
                        break;
                        default:
                        $sql_join .= "";
                        $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.middlename LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.lastname LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.email LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
                    }
                } else {
                    $sql_join .= "";
                    $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR cp.middlename LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.lastname LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%' OR cp.email LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";

                }
            break;
        }
    }

    if($filter == "active"){
        $sql_where .= " AND cp.content_status < 2";
    }  else if ($filter == "deleted") {
        $sql_where .= " AND cp.content_status = 2";
    } else {
        $sql_where .= " AND cp.content_status < 2";
    }


    $sql = "SELECT cp.*
             FROM contactperson cp
            ".$sql_join."
            WHERE (cp.id is not null)".$sql_where;
    if($customer_id != null){
        $list = array();
        $sql .= " GROUP BY cp.id ORDER BY cp.name ASC".$pager;

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
        $sql .= " GROUP BY cp.id";
        if($page == 0 && $perPage == 0){
            $rowCount = 0;
            $o_query = $o_main->db->query($sql);
            if($o_query){
                $rowCount = $o_query->num_rows();
            }
            return $rowCount;
        } else {
            $sql .= " ORDER BY cp.name ASC".$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
