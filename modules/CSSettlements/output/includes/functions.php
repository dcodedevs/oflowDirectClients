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
	$pager = "";
    // $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();


    if($filter == "payments"){
        $sql_join = "";
        $sql_where = " AND (cmv.settlement_id = 0 OR cmv.settlement_id is null)";

        if($page == 0 && $perPage == 0){
            $pager = "";
        }
        $sql = "SELECT cmt.*, cmv.date, cmv.case_id, cb.name as bookaccountName
                 FROM cs_mainbook_transaction cmt
				 JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
				 JOIN cs_bookaccount cb ON cb.id = cmt.bookaccount_id
                ".$sql_join."
                WHERE cb.is_creditor_ledger = 1 AND ABS(cmt.amount) > 0 ".$sql_where;

        if($customer_id != null){
            $list = array();
            $sql .= " GROUP BY cmt.id ORDER BY cmv.date ASC".$pager;

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
            $sql .= " GROUP BY cmt.id";
            if($page == 0 && $perPage == 0){
                $rowCount = 0;
                $o_query = $o_main->db->query($sql);
                if($o_query){
                    $rowCount = $o_query->num_rows();
                }
                return $rowCount;
            } else {
                $sql .= " ORDER BY cmv.date ASC".$pager;
                $f_check_sql = $sql;

                $o_query = $o_main->db->query($sql);
                if($o_query && $o_query->num_rows()>0){
                    $list = $o_query->result_array();
                }
                return $list;
            }
        }
    } else if($filter == "settlements") {
        $sql_join = "";
        $sql_where = " ";

        if($page == 0 && $perPage == 0){
            $pager = "";
        }
        $sql = "SELECT cs.*
                 FROM cs_settlement cs
                ".$sql_join."
                WHERE 1=1 ".$sql_where;
        if($customer_id != null) {
            $list = array();
            $sql .= " GROUP BY cs.id ORDER BY cs.date DESC, cs.id desc".$pager;

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
            $sql .= " GROUP BY cs.id";
            if($page == 0 && $perPage == 0){
                $rowCount = 0;
                $o_query = $o_main->db->query($sql);
                if($o_query){
                    $rowCount = $o_query->num_rows();
                }
                return $rowCount;
            } else {
                $sql .= " ORDER BY cs.date DESC, cs.id desc".$pager;
                $f_check_sql = $sql;

                $o_query = $o_main->db->query($sql);
                if($o_query && $o_query->num_rows()>0){
                    $list = $o_query->result_array();
                }
                return $list;
            }
        }
    }


}
