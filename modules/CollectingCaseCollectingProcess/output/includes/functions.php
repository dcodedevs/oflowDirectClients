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

    $sql = "SELECT cp.*
             FROM collecting_cases_collecting_process cp
            ".$sql_join."
            WHERE 1=1 ".$sql_where;
    if($customer_id != null){
        $list = array();
        $sql .= " GROUP BY cp.id ORDER BY cp.sortnr ASC".$pager;

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
            $sql .= " ORDER BY cp.sortnr ASC".$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
