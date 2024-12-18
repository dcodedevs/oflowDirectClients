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



    // foreach($filters as $filterName=>$filterValue){
    //     switch($filterName){
    //         case "responsibleperson_filter":
    //             if($filterValue > 0){
    //                 $sql_join .= "";
    //                 $sql_where .= " AND cp.projectLeader = ".$o_main->db->escape($filterValue);
    //             }
    //         break;
    //         case "search_filter":
    //             if(is_array($filterValue)){
    //                  switch($filterValue[0]){
    //                     case 1:
    //                     $sql_join .= "";
    //                     $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
    //                     break;
    //                     case 2:
    //                     $sql_join .= "";
    //                     $sql_where .= " AND (c.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
    //                     break;
    //                     break;
    //                     default:
    //                     $sql_join .= "";
    //                     $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
    //                 }
    //             } else {
    //                 $sql_join .= "";
    //                 $sql_where .= " AND (cp.name LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
    //             }
    //         break;
    //     }
    // }

    if($filter == "not_printed"){
        $sql = "SELECT c.*, a.id AS action_id FROM collecting_cases_handling_action a
        JOIN collecting_cases c ON c.id = a.collecting_case_id
        LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
        WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
        AND (a.action_type = 1 OR (a.action_type = 4 AND (cust.invoiceEmail = '' or cust.invoiceEmail is null))) AND a.collecting_cases_process_steps_action_id is not null
        ORDER BY a.created DESC";

        if($customer_id != null){
            $list = array();
            $sql .= " ".$pager;

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
            $sql .= " ";
            if($page == 0 && $perPage == 0){
                $rowCount = 0;
                $o_query = $o_main->db->query($sql);
                if($o_query){
                    $rowCount = $o_query->num_rows();
                }
                return $rowCount;
            } else {
                $sql .= " ".$pager;
                $f_check_sql = $sql;

                $o_query = $o_main->db->query($sql);
                if($o_query && $o_query->num_rows()>0){
                    $list = $o_query->result_array();
                }
                return $list;
            }
        }
    } else if($filter == "printed_today"){

        $sql = "SELECT cb.*, count(cl.id) as pdf_count
                 FROM collecting_cases_batch cb
                 JOIN collecting_cases_claim_letter cl ON cl.batch_id = cb.id
                WHERE 1=1 AND DATE(cb.created) = CURDATE()";
        if($customer_id != null){
            $list = array();
            $sql .= " GROUP BY cl.id ORDER BY cb.created ASC".$pager;

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
            $sql .= " GROUP BY cb.id";
            if($page == 0 && $perPage == 0){
                $rowCount = 0;
                $o_query = $o_main->db->query($sql);
                if($o_query){
                    $rowCount = $o_query->num_rows();
                }
                return $rowCount;
            } else {
                $sql .= " ORDER BY cb.created ASC".$pager;
                $f_check_sql = $sql;

                $o_query = $o_main->db->query($sql);
                if($o_query && $o_query->num_rows()>0){
                    $list = $o_query->result_array();
                }
                return $list;
            }
        }
    } else if($filter == "printed_earlier"){
        $sql = "SELECT cb.*, count(cl.id) as pdf_count
                 FROM collecting_cases_batch cb
                 JOIN collecting_cases_claim_letter cl ON cl.batch_id = cb.id
                ".$sql_join."
                WHERE 1=1 AND DATE(cb.created) <> CURDATE()".$sql_where;
        if($customer_id != null){
            $list = array();
            $sql .= " GROUP BY cb.id ORDER BY cb.created ASC".$pager;

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
            $sql .= " GROUP BY cb.id";
            if($page == 0 && $perPage == 0){
                $rowCount = 0;
                $o_query = $o_main->db->query($sql);
                if($o_query){
                    $rowCount = $o_query->num_rows();
                }
                return $rowCount;
            } else {
                $sql .= " ORDER BY cb.created ASC".$pager;
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