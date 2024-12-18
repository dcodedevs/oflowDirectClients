<?php
function get_customer_list_count2($o_main, $filter, $search_filter, $search_by){
    return get_customer_list($o_main, $filter, $search_filter, $search_by, 0, 0);
}
function get_customer_list_count($o_main, $filter, $search_filter,  $search_by){
    $search_filter = "";
    $search_by = "";
    return get_customer_list($o_main, $filter,  $search_filter, $search_by, 0, 0);
}
function get_customer_list($o_main, $filter, $search_filter, $search_by, $page=1, $perPage=100, $customer_id = null) {

    $s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0){
        $orders_module_id_find = $o_query->row_array();
        $orders_module_id = $orders_module_id_find["uniqueID"];
    }

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $search_filter_sql_join = "";
    $search_filter_sql_where = "";
    $city_sql_where = "";
    $with_orders_sql_where = "";
    $selfdefinedfield_sql_where = "";
    $selfdefinedfield_sql_join = "";
    $with_orders_sql_join="";
    $activecontract_sql_where = "";
    $activecontract_sql_join="";

    if ($search_filter) {
        switch($search_by){
            case 1:
            $search_filter_sql_join = "";
            $search_filter_sql_where = " AND (keycard.keycardNumber LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR keycard.keycard_number_hex LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
            break;
            case 2:
            $search_filter_sql_join = "";
            $search_filter_sql_where = " AND (c.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
            break;
            case 3: 
            $search_filter_sql_join = "";
            $search_filter_sql_where = " AND (cp.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
            break;

            default:
            $search_filter_sql_join = "";
            $search_filter_sql_where = " AND (keycard.keycardNumber LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR keycard.keycard_number_hex LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
        }
    }
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    if($filter == "active"){
        $with_orders_sql_join = " ";
        $with_orders_sql_where = " AND keycard.deactivatedTime is null";
    } 

    $showDefaultList = false;
    if($filter == "all") {
        $showDefaultList = true;
    }

    $sql = "SELECT keycard.*, c.name customerName, cp.name contactPersonName  
            FROM contactperson_keycard_log keycard
            LEFT OUTER JOIN contactperson cp ON cp.id = keycard.contactpersonId
            LEFT OUTER JOIN customer c ON cp.customerId = c.id 
            ".$with_orders_sql_join.$search_filter_sql_join."
            WHERE 1=1 ".$search_filter_sql_where.$with_orders_sql_where;

    $sql .= " GROUP BY keycard.id";
    if($page == 0 && $perPage == 0){
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query){
            $rowCount = $o_query->num_rows();
        }
        return $rowCount;
    } else { 
        $sql .= " ORDER BY keycard.keycardNumber ASC, keycard.id DESC".$pager;
        $f_check_sql = $sql;
        
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $list = $o_query->result_array();
        }
        return $list;
    }
}