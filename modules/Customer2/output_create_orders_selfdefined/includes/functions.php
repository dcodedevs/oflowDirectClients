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

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_where = "";

    // if ($search_filter) {
    //     switch($search_by){
    //         case 1:
    //         $search_filter_sql_join = "";
    //         $search_filter_sql_where = " AND (keycard.keycardNumber LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
    //         break;
    //         case 2:
    //         $search_filter_sql_join = "";
    //         $search_filter_sql_where = " AND (c.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
    //         break;
    //         case 3:
    //         $search_filter_sql_join = "";
    //         $search_filter_sql_where = " AND (cp.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
    //         break;
    //
    //         default:
    //         $search_filter_sql_join = "";
    //         $search_filter_sql_where = " AND (keycard.keycardNumber LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
    //     }
    // }
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    $sql_where .= " AND p.date > DATE(NOW()) AND (p.type = 0 OR p.type is null)";

    $sql = "SELECT p.*, c.name  as customerName, CONCAT(COALESCE(emp.name,''), ' ', COALESCE(emp.middlename,''), ' ', COALESCE(emp.lastname,'')) workplaceLeaderName,
            CONCAT(COALESCE(emp2.name,''), ' ', COALESCE(emp2.middlename,''), ' ', COALESCE(emp2.lastname,'')) projectLeaderName
             FROM project2 p
             LEFT JOIN customer c ON c.id = p.customerId
             LEFT JOIN contactperson emp ON p.workplaceleaderId = emp.id
             LEFT JOIN contactperson emp2 ON p.employeeId = emp2.id
            WHERE 1=1".$sql_where;

    $sql .= " GROUP BY p.id";
    if($page == 0 && $perPage == 0){
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query){
            $rowCount = $o_query->num_rows();
        }
        return $rowCount;
    } else {
        $sql .= " ORDER BY p.date ASC".$pager;
        $f_check_sql = $sql;
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $list = $o_query->result_array();
        }
        return $list;
    }
}
